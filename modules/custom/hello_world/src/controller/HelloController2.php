<?php
/**
 * @file
 * Contains \Drupal\hello_world\Controller\HelloController.
 */
namespace Drupal\hello_world\Controller;
class HelloController {

  public function testAPI() {
    $url='https://sjc1.qualtrics.com/API/v3/survey-definitions/SV_57I7D9UFWxPMqxM';

    try {
      $response = \Drupal::httpClient()->get($url, 
      array('headers' => array('X-API-Token' => 'HR6nK5nz49glCHlmtQdfcFfgPQHF79PcoFJqABbj','Accept' => 'text/plain')));
      $data = (string) $response->getBody();
      if (empty($data)) {
        return FALSE;
      }
    }
    catch (RequestException $e) {
      return FALSE;
    }
    return $data;

  }
  public function content() {
    $data=$this->testAPI();
    return array(
      '#type' => 'markup',
      '#markup' => t('Hello, World! new' . $data),
    );
  }
 /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $default_config = \Drupal::config('hello_world.settings');
    return [
      'hello_block_name' => $default_config->get('hello.name'),
    ];
  }
}
/*
user = $user;
        $this->token = $token;
        $this->basepath = $basepath;
        $this->format = $format;
        $this->version = $version;
        $this->requestDefaults = 
        array('User' => $this->user,'Token' => 
        $this->token,'Format' => $this->format ,'Version' => 
        $this->version);
    }

    $qualtrics = new Qualtrics('user_name','token','JSON',
    'https://xxx.qualtrics.com/WRAPI/ControlPanel/api.php',
    '2.4');

print_r($qualtrics->getSurvey(array('SurveyID' => 'SV_xxxxx')));
Methods and parameters are case sensitive in Qualtrics API.
*/