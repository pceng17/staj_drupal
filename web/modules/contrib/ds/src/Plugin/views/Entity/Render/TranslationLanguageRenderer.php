<?php

namespace Drupal\ds\Plugin\views\Entity\Render;

use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ResultRow;

/**
 * Renders entity translations in their row language.
 */
class TranslationLanguageRenderer extends DefaultLanguageRenderer {

  /**
   * Stores the field alias of the langcode column.
   *
   * @var string
   */
  protected $langcodeAlias;

  /**
   * {@inheritdoc}
   */
  public function query(QueryPluginBase $query, $relationship = NULL) {
    // There is no point in getting the language, in case the site is not
    // multilingual.
    if (!$this->languageManager->isMultilingual()) {
      return;
    }
    // If the data table is defined, we use the translation language as render
    // language, otherwise we fall back to the default entity language, which is
    // stored in the revision table for revisionable entity types.
    $langcode_key = $this->entityType->getKey('langcode');
    foreach (['data_table', 'revision_table', 'base_table'] as $key) {
      if ($table = $this->entityType->get($key)) {
        $table_alias = $query->ensureTable($table);
        $this->langcodeAlias = $query->addField($table_alias, $langcode_key);
        break;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(array $result) {
    parent::dsPreRender($result, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function getLangcode(ResultRow $row) {
    return $row->{$this->langcodeAlias} ?? $this->languageManager->getDefaultLanguage()
      ->getId();
  }

}
