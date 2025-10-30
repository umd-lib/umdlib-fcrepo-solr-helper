<?php

namespace Drupal\umdlib_fcrepo_solr_helper\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;
use Drupal\Core\Render\Markup;
use Drupal\Component\Render\MarkupInterface;

/**
 * Twig extension providing custom functionalities.
 *
 * @package Drupal\umdlib_fcrepo_solr_helper\TwigExtension
 */
class FCRepoTwigExtension extends AbstractExtension {
  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'umdlib_fcrepo_query.twig_extension';
  }

  public function getFunctions() {
    return [
        new TwigFunction('fc_url_query', [
          $this,
          'getUrlQuery'
        ]),
        new TwigFunction('compare_markup_values', [
          $this,
          'compareMarkupValues'
        ])
    ];
  }

  public function compareMarkupValues($field, $compare) {
    if ($field instanceof Markup) {
      $field = $field->__toString();
    } elseif (is_array($field)) {
      $field = reset($field);
      foreach ($field as $k => $v) {
        if (is_string($v)) {
          $field = $v;
          break;
        }
      }
    }
    if ($compare instanceof Markup) {
      $compare = $compare->__toString();
    }
    if (is_string($field) && is_string($compare)) {
      return str_contains($field, $compare);
    }
    return false;
  }

  public function getUrlQuery() {
    $full_uri = \Drupal::request()->getRequestUri();
    if (!empty($full_uri) && str_contains($full_uri, 'q=')) {
      $trunc_uri = explode('?', $full_uri);
      parse_str(!empty($trunc_uri[1]) ? $trunc_uri[1] : $full_uri, $uri_array);
      foreach ($uri_array as $key => $value) {
        if ($key == 'q') {
          return $value;
        }
      }
    }
    return;
  }

  public function getFilters() {
    return [
      new TwigFilter(
        'landing_page',
        [$this, 'getCollectionLandingPage']
      ),
    ];
  }

  public function getCollectionLandingPage(string $collection) {
    $lp_map = [
      "UMD Student Newspapers" => "",
      "Katherine Anne Porter Correspondence" => "Article",
      "National Trust Library Postcards" => "Audio",
      "Prange Children's Books" => "Audio",
      "Prange Posters and Wall Newspapers" => "Audio Book",
      "US Government Posters" => "Book",
      "Diamondback Photographs" => "CD",
      "Labor" => "Computer File",
      "Advancing Workers' Rights" => "DVD",
      "Commercial Broadcasting" => "eBook",
      "Cultural Preservation" => "eMusic",
      "Literature and Rare Books" => "eVideo",
      "Music, Dance, and Theater at UMD" => "Image",
      "Performing Arts" => "Journal",
      "Politics and Civic Life" => "Journal",
      "Postwar Japan" => "LP",
      "Public Broadcasting" => "Map",
      "Punk Collections" => "Newspaper",
      "Maryland and Historical Collections" => "Score",
      "thesis" => "Thesis",
      "video_recording" => "Video Recording",
      "other" => "Other",
      "database" => "Database",
      "web_page" => "Webpage",
      "book_chapter" => "Book Chapter",
      "conference_proceeding" => "Conference Proceeding",
      "dissertation" => "Dissertation",
      "kit" => "Kit",
      "manuscript" => "Manuscript",
      "text_resource" => "Text Resource",
      "video" => "Video",
      "web_resource" => "Web Resource",
    ];
    return !empty($lp_map[$collection]) ? $lp_map[$collection] : null;
  }


}
