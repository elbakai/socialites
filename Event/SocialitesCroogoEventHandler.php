<?php

App::uses('CakeEventListener', 'Event');
App::uses('SocialitesBaseEventHandler', 'Socialites.Event');
App::uses('CroogoProvider', 'Socialites.Provider');

/**
 * SocialitesCroogoEventHandler
 */
class SocialitesCroogoEventHandler extends SocialitesBaseEventHandler
	implements CakeEventListener {

/**
 * implementedEvents
 */
	public function implementedEvents() {
		return array(
			'Socialites.oauthCallback' => array(
				'callable' => 'onCallback',
			),
		);
	}

	public function getProviderClass() {
		return 'CroogoProvider';
	}

	public function getProvider() {
		$config = $this->getConfig();
		if ($config) {
			return new CroogoProvider($config);
		}
	}

	public function onCallback($event) {
		if (!$this->_isValidEvent($event)) {
			return;
		}

		$oauthClient = $this->getProvider();
		$controller = $event->subject;

		$token = $oauthClient->getAccessToken('authorization_code', array(
			'code' => $controller->request->query('code'),
		));

		$oauthUser = $oauthClient->getUserDetails($token);

		$user = $this->_findLocalUser($oauthUser);
		$this->_prepareUser(compact('event', 'token', 'oauthUser'));

		if (empty($user)) {
			return $controller->redirect($this->_addUserUrl);
		}

		return compact('token', 'oauthUser', 'user');
	}

}
