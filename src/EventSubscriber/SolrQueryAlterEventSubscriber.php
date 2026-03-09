<?php

namespace Drupal\umdlib_fcrepo_solr_helper\EventSubscriber;

use Drupal\search_api_solr\Event\PreQueryEvent;
use Drupal\search_api_solr\Event\PostExtractResultsEvent;
use Drupal\search_api_solr\Event\PostConvertedQueryEvent;
use Drupal\search_api_solr\Event\SearchApiSolrEvents;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Query\QueryInterface as SapiQueryInterface;
use Solarium\Core\Query\QueryInterface as SolariumQueryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\search_api\Item\Field as SearchField;
use Solarium\Component\Result\Highlighting;

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
      SearchApiSolrEvents::POST_CONVERT_QUERY => 'postQuery',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function postExtractResults(PostExtractResultsEvent $event): void {
    $query = $event->getSearchApiQuery();
    $query = $event->getSolariumQuery();
    // dsm($query);
    return;

    $search_index = $query->getIndex();
    if (empty($search_index)) {
      return;
    }

    $res = $event->getSolariumResult();
    if (empty($res)) {
      return;
    }
    $highlights_raw = $res->getHighlighting();
    if (empty($highlights_raw)) {
      return;
    }
    $highlights = $highlights_raw->getResults();
    if (empty($highlights)) {
      return;
    }
    $res2 = $event->getSearchApiResultSet();
    if (empty($res2)) {
      return;
    }
    $items = $res2->getResultItems();

    foreach ($items as $key => $item) {
      $short_key = str_replace('solr_document/', '', $key);
      if (!empty($highlights[$short_key])) {
        $h_fields = $highlights[$short_key]->getFields();
        $f = NULL;
        foreach ($h_fields as $h_key => $h_val) {
          if (is_array($h_val)) {
            $h_val = reset($h_val);
          }
          if (!empty($h_val)) {
            $item->setExcerpt($h_val);
          }
          
          // if (!empty($h_val)) {
          //   dsm($h_key);
          //   dsm($val);
          //   $f = new SearchField($search_index, $h_key);
          //   $f->setValues($h_val);
          // }
          // if (!empty($f)) {
          //   // $item->setField($h_key, $f);
          //   $item->setExcerpt(reset($h_val));
          // }

        }
      }
    }
    $res2->setResultItems($items);
    return;

    $server_id = $search_index->getServerId();
    if ($server_id != "fcrepo") {
      return;
    }

    $sapi_results = $event->getSearchApiResultSet();
    if ($sapi_results->getResultCount() < 1) {
      return;
    }

    // $index_id = $search_index->id();
    // dsm($index_id);
    // if ($index_id == 'searcher') {
    //   $result_items = $sapi_results->getResultItems();
    //   foreach ($result_items as $key => $item) {
    //     dsm($item);
    //     break;
    //   }
    // }



    // This is potentially useful code for digging into nested
    // results. Not currently used but keeping for future use.
    //
    // if (!empty($search_index)) {
    //   $index_id = $search_index->id();
    //   if (!str_contains($index_id, "_nested")) {
    //     return;
    //   }
    // }
    // If an index contains _nested, we process nested fields.

    // $result_items = $results->getResultItems();
    // foreach ($result_items as $key => $item) {
    //   $extra = $item->getExtraData('search_api_solr_document');
    //   // dsm($extra);
    //   if (!empty($extra)) {
    //     $object__rights_holder = $extra->__get('object__rights_holder');
    //     if (!empty($object__rights_holder)) {

    //     }
    //     $object__subjects = $extra->__get('object__subject');
    //     dsm($object__subjects);
    //     if (!empty($object__subjects)) {

    //     }
    //     $object__has_member = $extra->__get('object__has_member');
    //     dsm($object__has_member);
    //     if (!empty($object__has_member)) {

    //     }
    //     $item__rights_holder = $extra->__get('item__rights_holder');
    //     if (!empty($item__rights_holder)) {

    //     }
    //     $object__location = $extra->__get('object__location');
    //     if (!empty($object__location)) {

    //     }
    //     $object__has_file = $extra->__get('object__has_file');
    //     if (!empty($object__has_file)) {
          
    //     }
        // if (!empty($files['docs'])) {
        //   foreach ($files['docs'] as $f_doc) {
        //     if (!empty($f_doc['mime_type']) && $f_doc['mime_type'] == 'application/pdf') {
        //       $new_files_array[] = $f_doc['id'];
        //     }
        //   }
        // }
      // }
      // $pcdm_files = $item->getField('pcdm_files');
      // if (!empty($pcdm_files)) {
      //    $pcdm_files->setValues($new_files_array);
      // }
    // }
    // $results->setResultItems($result_items);
  }

  public function postQuery(PostConvertedQueryEvent $event): void {
    // $query = $event->getSolariumQuery();
    // $search_query = $event->getSearchApiQuery();
  }

  /**
   * {@inheritdoc}
   */
  public function preQuery(PreQueryEvent $event): void {
    $search_query = $event->getSearchApiQuery();
    $search_index = $search_query->getIndex();
    if (empty($search_index)) {
      return;
    }

    $server_id = $search_index->getServerId();
    if ($server_id != "fcrepo") {
      return;
    }

    $query = $event->getSolariumQuery();
    if (empty($query)) {
      return;
    }
    
    $query->createFilterQuery('published_filter')->setQuery('is_published:true');
    $index_id = $search_index->id();
    if ($index_id == 'searcher') {
      $query->createFilterQuery('discoverable_filter')->setQuery('is_discoverable:true');
      // Prevent WHPool from creeping into results
      // $query->createFilterQuery('whca_filter')->setQuery('-adminset__title__display:WHCA Pool Reports');
    }

    // Only request configured fields.
    $fields = $search_index->getFields();
    if (empty($fields)) {
      return;
    }
    $field_keys = [];
    foreach ($fields as $k => $f) {
      if ($f->getType() != 'text') {
        array_push($field_keys, $k);
      }  
    }
    $configured_fields = implode(',', $field_keys) . ',score,id';

    $query->setFields($configured_fields);
    return;

    // Add nested fields back to result.
    $index_id = $search_index->id();
    if (!str_contains($index_id, "_nested")) {
      return;
    }
    // If an index contains _nested, we add nested fields.
    $query->addField('[child]');
    $query->addField('object__rights_holder');
    $query->addField('object__subject');
    $query->addField('object__has_member');
    $query->addField('object__location');
    $query->addField('object__has_file'); 
  }
}
