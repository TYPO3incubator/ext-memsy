<?php

declare(strict_types=1);

namespace TYPO3Incubator\Memsy\Controller;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3Incubator\Memsy\Domain\Model\MembershipStatus;
use TYPO3Incubator\Memsy\Domain\Repository\MemberRepository;
use TYPO3Incubator\Memsy\Domain\Repository\MembershipRepository;
use TYPO3Incubator\Memsy\Payment\PaymentManagementAction;
use TYPO3Incubator\Memsy\Service\MembershipService;
use TYPO3Incubator\Memsy\Service\PaymentService;

#[AsController]
final class BackendMemberController extends ActionController
{
    private ModuleTemplate $moduleTemplate;
    private LanguageService $languageService;

    protected const string MEMBER_ACTION_SET_ACTIVE = 'setActive';
    protected const string MEMBER_ACTION_SET_INACTIVE = 'setInactive';
    protected const string MEMBER_ACTION_MARK_AS_PAID = 'markAsPaid';

    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly MemberRepository        $memberRepository,
        private readonly MembershipService       $membershipService,
        private readonly PageRenderer            $pageRenderer,
        protected readonly IconFactory           $iconFactory,
        private readonly PaymentService          $paymentService,
        private readonly LanguageServiceFactory  $languageServiceFactory,
        private readonly MembershipRepository    $membershipRepository,
    )
    {
        $this->languageService = $this->languageServiceFactory->createFromUserPreferences(null);
    }

    protected function initializeAction(): void
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->membershipService->setRequest($this->request);
    }

    public function indexAction(): ResponseInterface
    {
        $site = $this->request->getAttribute('site');

        if (!($site instanceof Site)) {
            return $this->moduleTemplate->renderResponse('Backend/Overview');
        }

        $this->getDocHeaderButtons($this->moduleTemplate);
        $this->pageRenderer->loadJavaScriptModule('@vendor/typo3-incubator/memsy/backend.js');
        $this->pageRenderer->addCssFile('EXT:memsy/Resources/Public/Css/backend.css');

        // filter fields
        $filters = [];
        $search = '';
        if ($this->request->hasArgument('search')) {
            $search = $this->request->getArgument('search');
            $filters['search'] = $search;
            // you found the beer - CHEERS
            if ($search === '🍺' || $search === '🍻') {
                $filters['search'] = 'Jochen';
            }
        }
        $membershipUid = 0;
        if ($this->request->hasArgument('membershipUid')) {
            $membershipUid = (int)$this->request->getArgument('membershipUid');;
            $filters['membershipUid'] = $membershipUid;
        }

        $membershipStatus = -2;
        if ($this->request->hasArgument('membershipStatus')) {
            $membershipStatus = (int)$this->request->getArgument('membershipStatus');
            $filters['membershipStatus'] = $membershipStatus;
        }

        // sorting fields
        $fieldMap = [
            'lastName' => 'lastName',
            'membershipTitle' => 'membership.title',
            'membershipStatus' => 'membershipStatus',
            'email' => 'email',
        ];

        $sortField = $this->request->hasArgument('sortField')
            ? $this->request->getArgument('sortField')
            : 'lastName';

        if (!isset($fieldMap[$sortField])) {
            $sortField = 'lastName';
        }

        $realField = $fieldMap[$sortField];

        $sortDirection = QueryInterface::ORDER_ASCENDING;

        if ($this->request->hasArgument('sortDirection')) {
            $sortDirection = $this->request->getArgument('sortDirection');
            $sortDirection = in_array($sortDirection, [QueryInterface::ORDER_ASCENDING, QueryInterface::ORDER_DESCENDING])
                ? $sortDirection
                : QueryInterface::ORDER_ASCENDING;
        }

        $orderings = [
            $realField => $sortDirection,
        ];
        $siteSettings = $site->getSettings();
        $membersPid = (int)$siteSettings->get('felogin.pid');
        $membershipPid = (int)$siteSettings->get('memsy.storage.membershipsFolderPid');

        $members = $this->memberRepository->findByFilters($filters, $orderings, $membersPid);
        $itemsPerPage = 20;
        $currentPage = $this->request->hasArgument('currentPageNumber')
            ? (int)$this->request->getArgument('currentPageNumber')
            : 1;
        $maximumLinks = 15;
        $paginator = new QueryResultPaginator($members, $currentPage, $itemsPerPage);
        $pagination = new SlidingWindowPagination(
            $paginator,
            $maximumLinks,
        );
        $memberships = $this->membershipRepository->findAllByStorageId($membershipPid);

        $statusOptions = [
            [
                'value' => -2,
                'label' => 'All',
            ],
            [
                'value' => MembershipStatus::Pending->value,
                'label' => MembershipStatus::Pending->label(),
            ],
            [
                'value' => MembershipStatus::Active->value,
                'label' => MembershipStatus::Active->label(),
            ],
            [
                'value' => MembershipStatus::Inactive->value,
                'label' => MembershipStatus::Inactive->label(),
            ],
        ];

        // get nextSortDirections
        $nextSortDirections = [];
        foreach (array_keys($fieldMap) as $alias) {
            $nextSortDirections[$alias] = ($sortField === $alias && $sortDirection === QueryInterface::ORDER_ASCENDING)
                ? QueryInterface::ORDER_DESCENDING
                : QueryInterface::ORDER_ASCENDING;
        }

        $this->moduleTemplate->assignMultiple(
            [
                'pagination' => $pagination,
                'paginator' => $paginator,
                'search' => $search,
                'membershipUid' => $membershipUid,
                'membershipStatus' => $membershipStatus,
                'memberships' => $memberships,
                'statusOptions' => $statusOptions,
                'sortField' => $sortField,
                'sortDirection' => $sortDirection,
                'nextSortDirections' => $nextSortDirections,
            ]
        );

        return $this->moduleTemplate->renderResponse('Backend/Index');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function generateSepaXmlAction(): ResponseInterface
    {
        $this->paymentService->setRequest($this->request);
        $sepaXML = $this->paymentService->generateSepaXml();

        if ($sepaXML) {
            $response = $this->responseFactory->createResponse();

            $response->getBody()->write($sepaXML);

            $site = $this->request->getAttribute('site');
            $siteSettings = $site->getSettings();
            $organizationName = $siteSettings->get('memsy.organization.name');
            $filename = "SEPA - $organizationName.xml";

            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
            if ($origin) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
                $response->headers->set('Access-Control-Allow-Credentials', 'true');
            }

            return $response
                ->withHeader('Content-Type', 'text/xml; charset=utf-8')
                ->withHeader('Content-Disposition', 'attachment; filename="' . $filename)
                ->withHeader('Cache-Control', 'private, no-cache, no-store, must-revalidate')
                ->withHeader('Pragma', 'no-cache');
        }

        return $this->moduleTemplate->renderResponse('Backend/Index');
    }

    protected function getDocHeaderButtons(ModuleTemplate $view): void
    {
        $buttonBar = $view->getDocHeaderComponent()->getButtonBar();

        $buttonBar->addButton($this->getDocHeaderButtonForGeneratingSepaXml($buttonBar), ButtonBar::BUTTON_POSITION_LEFT, 10);
        $buttonBar->addButton($this->getDocHeaderButtonForSendingPaymentReminder($buttonBar), ButtonBar::BUTTON_POSITION_LEFT, 10);
    }

    private function getDocHeaderButtonForGeneratingSepaXml(ButtonBar $buttonBar): LinkButton
    {
        $href = $this->uriBuilder->reset()->uriFor('generateSepaXml');

        return $buttonBar->makeLinkButton()
            ->setHref($href)
            ->setTitle($this->languageService->sL('LLL:EXT:memsy/Resources/Private/Language/locallang_mod_member.xlf:downloadSepaXmlButton'))
            ->setShowLabelText(true)
            ->setIcon($this->iconFactory->getIcon('actions-download', IconSize::SMALL));
    }

    public function memberBulkActionAction(array $memberUids = [], string $memberAction = null): ResponseInterface
    {
        if (empty($memberUids) || $memberAction === null) {
            $this->addFlashMessage(
                'No items selected or no action specified.',
                '',
                ContextualFeedbackSeverity::INFO,
            );
            return $this->redirect('index');
        }
        switch ($memberAction) {
            case self::MEMBER_ACTION_SET_ACTIVE:
                $this->membershipService->setMembersActive($memberUids);
                $this->addFlashMessage(
                    'Successfully marked all selected members as active.',
                    'Set members active',
                );
                break;
            case self::MEMBER_ACTION_SET_INACTIVE:
                $this->membershipService->setMembersInactive($memberUids);
                $this->addFlashMessage(
                    'Successfully marked all selected members as inactive.',
                    'Set members inactive',
                );
                break;
            case self::MEMBER_ACTION_MARK_AS_PAID:
                $this->paymentService->markMembersAsPaid($memberUids);
                $this->addFlashMessage(
                    'Successfully marked payments of all selected members as paid.',
                    'Mark as paid',
                );
                break;

        }
        return $this->redirect('index');
    }

    public function sendPaymentReminderAction(): ResponseInterface
    {
        $this->paymentService->setRequest($this->request);
        if ($this->request->hasArgument('memberUid')) {
            $memberUid = (int)$this->request->getArgument('memberUid');
            $member = $this->memberRepository->findByUid($memberUid);
            $members = [$member];
        } else {
            $members = $this->memberRepository->findAll();
        }
        $allSent = true;
        foreach ($members as $member) {
            $result = $this->paymentService->processMemberPayments($member);
            if ($result->action === PaymentManagementAction::ReminderMailCouldNotBeSent) {
                $allSent = false;
            }
        }
        if ($allSent) {
            $this->addFlashMessage('All payment reminders have been sent out');
        } else {
            $this->addFlashMessage('There was an error in sending out some payment reminders');
        }
        return $this->redirect('index');
    }

    private function getDocHeaderButtonForSendingPaymentReminder(ButtonBar $buttonBar): LinkButton
    {
        $href = $this->uriBuilder->reset()->uriFor('sendPaymentReminder');

        return $buttonBar->makeLinkButton()
            ->setHref($href)
            ->setTitle($this->languageService->sL('LLL:EXT:memsy/Resources/Private/Language/locallang_mod_member.xlf:sendPaymentReminder'))
            ->setShowLabelText(true)
            ->setIcon($this->iconFactory->getIcon('actions-paperplane', IconSize::SMALL));
    }

}
