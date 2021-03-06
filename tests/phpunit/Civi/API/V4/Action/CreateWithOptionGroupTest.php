<?php
namespace Civi\API\V4\Action;

use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;
use Civi\Api4\Contact;

/**
 * @group headless
 */
class CreateWithOptionGroupTest extends BaseCustomValueTest {

  /**
   * Remove the custom tables
   */
  public function setUp() {
    $this->dropByPrefix('civicrm_value_financial');
    $this->dropByPrefix('civicrm_value_favorite');
    parent::setUp();
  }

  public function testGetWithCustomData() {
    $customGroup = CustomGroup::create()
      ->setCheckPermissions(FALSE)
      ->setValue('name', 'FavoriteThings')
      ->setValue('title', 'FavoriteThings') // todo default to name if not set
      ->setValue('extends', 'Contact')
      ->execute();

    $customGroupId = $customGroup->getArrayCopy()['id'];

    CustomField::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'FavColor')
      ->setValue('options', ['r' => 'Red', 'g' => 'Green', 'b' => 'Blue'])
      ->setValue('custom_group_id', $customGroupId)
      ->setValue('html_type', 'Select')
      ->setValue('data_type', 'String')
      ->execute();

    CustomField::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'FavFood')
      ->setValue('options', ['1' => 'Corn', '2' => 'Potatoes', '3' => 'Cheese'])
      ->setValue('custom_group_id', $customGroupId)
      ->setValue('html_type', 'Select')
      ->setValue('data_type', 'String')
      ->execute();

    $customGroup = CustomGroup::create()
      ->setCheckPermissions(FALSE)
      ->setValue('name', 'FinancialStuff')
      ->setValue('title', 'FinancialStuff') // todo default to name if not set
      ->setValue('extends', 'Contact')
      ->execute();

    $customGroupId = $customGroup->getArrayCopy()['id'];

    CustomField::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'Salary')
      ->setValue('custom_group_id', $customGroupId)
      ->setValue('html_type', 'Number')
      ->setValue('data_type', 'Money')
      ->execute();

    Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValue('first_name', 'Jerome')
      ->setValue('last_name', 'Tester')
      ->setValue('contact_type', 'Individual')
      ->setValue('FavoriteThings.FavColor', 'r')
      ->setValue('FavoriteThings.FavFood', '1')
      ->setValue('FinancialStuff.Salary', 50000)
      ->execute();

    $result = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('display_name')
      ->addSelect('FavoriteThings.FavColor')
      ->addSelect('FavoriteThings.FavColor.label')
      ->addSelect('FavoriteThings.FavFood.label')
      ->addSelect('FinancialStuff.Salary')
      ->addWhere('FavoriteThings.FavColor', '=', 'r')
      ->addWhere('FavoriteThings.FavFood.label', 'IN', ['Corn', 'Potatoes'])
      ->addWhere('FavoriteThings.FavFood', 'IN', [1, 2])
      ->addWhere('FinancialStuff.Salary', '>', '10000')
      ->execute()
      ->first();

    $this->assertEquals('r', $result['FavoriteThings.FavColor']);
    $this->assertEquals('Red', $result['FavoriteThings.FavColor.label']);
    $this->assertEquals('Corn', $result['FavoriteThings.FavFood.label']);
  }

}
