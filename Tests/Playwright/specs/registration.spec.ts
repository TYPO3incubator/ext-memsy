import { test, expect } from '@playwright/test';
import { members } from '../utils/members.js';

for (const index in members) {
    test('Registration ' + members[index].firstName, async ({ page }) => {
        await page.goto('/register-membership');
        if (members[index].title) {
            await page.locator('#title').fill(members[index].title);
        }

        if (members[index].gender) {
            await page.locator('#gender').selectOption(members[index].gender.toString());
        }

        await page.locator('#firstname').fill(members[index].firstName);
        await page.locator('#lastname').fill(members[index].lastName);
        await page.locator('#date_of_birth').fill(members[index].dateofbirth);
        await page.locator('#street').fill(members[index].street);
        await page.locator('#zip').fill(members[index].zip);
        await page.locator('#city').fill(members[index].city);
        await page.locator('#country').selectOption(members[index].countrycode);
        await page.locator('#email').fill(members[index].email);
        await page.locator('#telephone').fill(members[index].tel);

        if (members[index].iban) {
            await page.locator('#iban').fill(members[index].iban);
        }

        if (members[index].bic) {
            await page.locator('#bic').fill(members[index].bic);
        }

        if (members[index].sepa) {
            await page.locator('#sepa').check();
        }

        await page.locator('#membership').selectOption(members[index].membership.toString());

        await page.locator('#password').fill(members[index].password);
        await page.locator('#password-repeat').fill(members[index].password);

        await page.locator('#privacy').check();

        await page.locator('#submit').click();
        await expect(page.locator('.frame-type-memsy_createmembership')).toContainText('confirm your registration');
    });
}
