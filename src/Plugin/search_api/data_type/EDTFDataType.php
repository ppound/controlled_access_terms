<?php

namespace Drupal\controlled_access_terms\Plugin\search_api\data_type;

use Drupal\search_api\DataType\DataTypePluginBase;

/**
 * Provides a EDTF date range data type.
 *
 * @SearchApiDataType(
 *   id = "etdf_date_range",
 *   label = @Translation("EDTF Date range"),
 *   description = @Translation("Contains dates or date ranges and supports characters like ? ~."),
 *   fallback_type = "string",
 *   prefix = "dr"
 * )
 */
class EDTFDataType extends DataTypePluginBase {
}
