<?php

namespace Drupal\qualtricsxm;

use Drupal\Core\Config\ConfigFactory;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Helper class for Qualtrics API Call.
 */
class Qualtricsxm {

  private $apiBaseUrl;
  private $apiToken;

  /**
   * Qualtricsxm constructor.
   */
  public function __construct($api_base_url, ConfigFactory $configFactory) {
    $api_token = $configFactory->get('qualtricsxm.settings')->get('qualtricsxm_api_token');
    $this->apiBaseUrl = $api_base_url;
    $this->apiToken = $api_token ? $api_token : '';
  }

  /**
   * API call.
   *
   * @param array $url_params
   *   API URL params.
   *
   * @return string|bool
   *   Requested data.
   */
  public function httpRequest(array $url_params) {
    $options = [
      'timeout' => 15,
      'headers' => ['X-API-TOKEN' => $this->apiToken],
    ];

    $api_req = "/";
    foreach ($url_params as $url => $val) {
      $api_req .= urlencode($url) . "/" . urlencode($val);
    }

    $url = $this->apiBaseUrl . $api_req;

    $client = \Drupal::httpClient();

    try {
      $response = $client->request('GET', $url, $options);
    }
    catch (GuzzleException $e) {
      \Drupal::logger('qualtricsxm')->error($e->getMessage());
      return FALSE;
    }

    $code= $response->getStatusCode();

    if ($code == '200') {
      return $response->getBody()->getContents();
    }
    else {
      return FALSE;
    }
  }

  /**
   * Get survey by surveyID.
   *
   * @param string $survey_id
   *   Survey ID.
   *
   * @return bool|string
   *   FALSE or json data.
   */
  public function getSurvey($survey_id) {
    $survey = $this->httpRequest(["surveys" => $survey_id]);

    if (!$survey) {
      return "Qualtrics is not connected";
    }

    $survey_data = json_decode($survey);
    return $survey_data->result;
  }

  /**
   * Get survey list.
   *
   * @return bool|array
   *   TODO merge into getSurve.
   */
  public function getSurveyList() {
    $survey = $this->httpRequest(['surveys' => '']);
    if (!$survey) {
      return FALSE;
    }

    $survey_data = json_decode($survey);

    // Make sure the legacy function working, and renderable by theme_table.
    foreach ($survey_data->result->elements as $element) {
      $surveys_array[$element->id]['surveyname'] = $element->name;
      $surveys_array[$element->id]['id'] = $element->id;
      $surveys_array[$element->id]['ownerId'] = $element->ownerId;
      $surveys_array[$element->id]['lastModified'] = $element->lastModified;
      $surveys_array[$element->id]['isActive'] = $element->isActive;
    }

    return $surveys_array;
  }

  /**
   * Get extra submission meta data from API call.
   *
   * @param string $survey_id
   *   Survey ID.
   *
   * @return bool|string
   *   FALSE or json data.
   */
  public function getSubmissions($survey_id) {
    $request_data = $this->getSurvey($survey_id);

    if (!$request_data) {
      return FALSE;
    }
    $response_counts = !empty($request_data->responseCounts) ? $request_data->responseCounts : NULL;

    return $response_counts;
  }

}
