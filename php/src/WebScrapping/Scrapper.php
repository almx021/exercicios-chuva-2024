<?php

namespace Chuva\Php\WebScrapping;

use Chuva\Php\WebScrapping\Entity\Paper;
use Chuva\Php\WebScrapping\Entity\Person;

/**
 * Does the scrapping of a webpage.
 */
class Scrapper {

  /**
   * Loads paper information from the HTML and returns the array with the data.
   */
  public function scrap(\DOMDocument $dom): array {

    $dom_anchors = $dom->getElementsByTagName("a");

    $papers = [];
    foreach ($dom_anchors as $anchor) {
      if (str_contains($anchor->getAttribute("class"), "paper-card")) {
        $base_node = $anchor;

        $paper_title = $base_node->getElementsByTagName("h4")[0]->textContent;

        $authors = $base_node->getElementsByTagName("span");

        $paper_authors = [];
        foreach ($authors as $author) {
          if ($author->getAttribute("title") != '') {
            $formatted_name = preg_replace(['/\s{2,}/'], ' ', $author->textContent);
            $formatted_name = trim($formatted_name, ' ;');
            $formatted_name = ucwords(strtolower($formatted_name));

            $author_name = $formatted_name;
            $author_institutions = $author->getAttribute("title");

            $paper_authors[] = new Person($author_name, $author_institutions);
          }
        }

        $paper_type = '';
        $paper_id = '';
        $paper_divs = $base_node->getElementsByTagName("div");

        foreach ($paper_divs as $paper_div) {
          if ($paper_type == '') {
            if ($paper_div->getAttribute("class") == "tags mr-sm") {
              $paper_type = $paper_div->textContent;
              continue;
            }
          }
          else if ($paper_id == '') {
            if ($paper_div->getAttribute("class") == "volume-info") {
              $paper_id = $paper_div->textContent;
              continue;
            }
          }
          else
            break;
        }

        $papers[] = new Paper($paper_id, $paper_title, $paper_type, $paper_authors);
      }
    }

    return $papers;
  }

}
