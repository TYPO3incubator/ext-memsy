<?php

declare(strict_types=1);

namespace TYPO3Incubator\Memsy\ViewHelpers;

use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class EditLinkViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function __construct(
        protected readonly UriBuilder $uriBuilder,
    ) {}

    public function initializeArguments(): void
    {
        $this->registerArgument('table', 'string', 'Table', true);
        $this->registerArgument('uid', 'int', 'Uid', true);
    }

    /**
     * @throws RouteNotFoundException
     */
    public function render(): string
    {
        $table = $this->arguments['table'];
        $uid = $this->arguments['uid'];
        return (string)$this->uriBuilder->buildUriFromRoute('record_edit', [
            'edit' => [
                $table => [
                    $uid => 'edit',
                ],
            ],
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
        ]);
    }
}
