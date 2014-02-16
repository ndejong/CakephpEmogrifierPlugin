<?php
App::uses('Emogrifier', 'Emogrifier.View');
App::uses('Controller', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class EmogrifierViewTest extends CakeTestCase {

/**
 * setUp
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		App::build(array(
			'View' => array(dirname(dirname(dirname(__file__))) . DS . 'test_app' . DS . 'View' . DS)
		));

		$this->CakeEmail = new CakeEmail();
		$this->CakeEmail->emailFormat('html');
		$this->CakeEmail->from(array('from@example.com' => 'test mail'));
		$this->CakeEmail->to('to@example.com');
		$this->CakeEmail->viewRender('Emogrifier.Emogrifier');
		$this->CakeEmail->template('content');
		$this->CakeEmail->transport('Debug');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		App::build();
	}

/**
 * test render with invalid html (DOMDocument will issue a warning)
 *
 * @return void
 **/
	public function testCssHandling() {
		$message = '<style>.b{font-weight: strong;}</style><p class="b">This is bold</p>';
		$expected = $this->createExpected('<p class="b" style="font-weight: strong;">This is bold</p>');
		$this->CakeEmail->send($message);
		$result = $this->CakeEmail->message('html');

		$this->assertEqual(h($expected), h($result));
	}

/**
 * test render with invalid html (DOMDocument will issue a warning)
 *
 * @return void
 **/
	public function testInvalidHtml() {
		$message = '<p>This & That</p>';
		$expected = $this->createExpected('<p>This &amp; That</p>');
		$this->CakeEmail->send($message);
		$result = $this->CakeEmail->message('html');

		$this->assertEqual(h($expected), h($result));
	}

	protected function createExpected($message) {
		return <<<EOH
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head><title>Emails/html</title></head>
<body>{$message}</body>
</html>
EOH;
	}
}
