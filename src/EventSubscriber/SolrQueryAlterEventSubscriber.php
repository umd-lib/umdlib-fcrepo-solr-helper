<?php

namespace Drupal\umdlib_fcrepo_solr_helper\EventSubscriber;

use Drupal\search_api_solr\Event\PreQueryEvent;
use Drupal\search_api_solr\Event\PostExtractResultsEvent;
use Drupal\search_api_solr\Event\SearchApiSolrEvents;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Query\QueryInterface as SapiQueryInterface;
use Solarium\Core\Query\QueryInterface as SolariumQueryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alters the query where necessary to implement business logic.
 *
 * @package Drupal\umdlib_fcrepo_solr_helper\EventSubscriber
 */
class SolrQueryAlterEventSubscriber implements EventSubscriberInterface {
  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      SearchApiSolrEvents::POST_EXTRACT_RESULTS => 'postExtractResults',
      SearchApiSolrEvents::PRE_QUERY => 'preQuery',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function postExtractResults(PostExtractResultsEvent $event): void {
    $query = $event->getSearchApiQuery();
    $search_index = $query->getIndex();
    if (!empty($search_index)) {
      $index_id = $search_index->id();
      if (!str_contains($index_id, "_nested")) {
        return;
      }
    }
    // If an index contains _nested, we process nested fields.

    $results = $event->getSolariumResult();
    $results = $event->getSearchApiResultSet();
    if ($results->getResultCount() < 1) {
      return;
    }
    $result_items = $results->getResultItems();
    foreach ($result_items as $key => $item) {
      $extra = $item->getExtraData('search_api_solr_document');
      // dsm($extra);
      if (!empty($extra)) {
        $object__rights_holder = $extra->__get('object__rights_holder');
        if (!empty($object__rights_holder)) {

        }
        $object__subjects = $extra->__get('object__subject');
        dsm($object__subjects);
        if (!empty($object__subjects)) {

        }
        $object__has_member = $extra->__get('object__has_member');
        dsm($object__has_member);
        if (!empty($object__has_member)) {

        }
        $item__rights_holder = $extra->__get('item__rights_holder');
        if (!empty($item__rights_holder)) {

        }
        $object__location = $extra->__get('object__location');
        if (!empty($object__location)) {

        }
        $object__has_file = $extra->__get('object__has_file');
        if (!empty($object__has_file)) {
          
        }
        // if (!empty($files['docs'])) {
        //   foreach ($files['docs'] as $f_doc) {
        //     if (!empty($f_doc['mime_type']) && $f_doc['mime_type'] == 'application/pdf') {
        //       $new_files_array[] = $f_doc['id'];
        //     }
        //   }
        // }
      }
      // $pcdm_files = $item->getField('pcdm_files');
      // if (!empty($pcdm_files)) {
      //    $pcdm_files->setValues($new_files_array);
      // }
    }
    // $results->setResultItems($result_items);
  }

    /**
   * {@inheritdoc}
   */
  public function preQuery(PreQueryEvent $event): void {
    $search_query = $event->getSearchApiQuery();
    $search_index = $search_query->getIndex();
    if ($search_index) {
      $index_id = $search_index->id();
      if (!str_contains($index_id, "_nested")) {
        return;
      }
    }
    // If an index contains _nested, we add nested fields.
    $query = $event->getSolariumQuery();
    $query->addField('[child]');
    $query->addField('object__rights_holder');
    $query->addField('object__subject');
    $query->addField('object__has_member');
    $query->addField('object__location');
    $query->addField('object__has_file'); 
  }
}