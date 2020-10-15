<?php
class ApiCest 
{
    // method yang tereksekusi sebelum setiap method yang lain
    public function _before(ApiTester $I)
    {
        // set http header yang diperlukan untuk berinteraksi dengan api
    	$I->haveHttpHeader('X-Api-Key', '611C24E2C54F4FA94E85930F977E0684');

        // custom header supaya api memakai konfigurasi testing, misalnya database testing
    	$I->haveHttpHeader('X-TEST-ENV', true);
        
        $this->data['token'] = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJHRU5fVVNFUiI6ImF6aHJ5IiwiR0VOX05BTUUiOiJBeiIsIkdFTl9FTUFJTCI6bnVsbH0.zC5J2UFDzVnzTGJqKlKF8EwC1vAPGuxraWxCvPbZbTg';
    }

    public function getItemCategory(ApiTester $I)
    {
    	$I->sendGET('/item-category?token=' . $this->data['token']);
    	$I->seeResponseCodeIs(200);
    	$I->seeResponseIsJson();
    }

    public function getRowItemCategory(ApiTester $I)
    {
        $I->sendGET('/item-category/row?id=1900000081&token=' . $this->data['token']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    public function _after(ApiTester $I)
    {
        echo $I->grabResponse();
    }
}