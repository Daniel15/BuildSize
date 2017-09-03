<?php

namespace App;

use Github\Api\ApiInterface;
use Github\ResultPager;

class ResultPagerWithCustomField extends ResultPager {

  // TODO: Send this upstream as a PR
  public function fetchAllUsingField(
    ApiInterface $api,
    $method,
    $field,
    array $parameters = array()
  ) {
    $perPage = $api->getPerPage();
    $api->setPerPage(100);

    $result = $this->callApi($api, $method, $parameters);
    $this->postFetch();

    $result = $result[$field];

    while ($this->hasNext()) {
      $next = $this->fetchNext();
      $result = array_merge($result, $next[$field]);
    }

    $api->setPerPage($perPage);
    return $result;
  }
}