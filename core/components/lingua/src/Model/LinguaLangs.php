<?php
namespace Lingua\Model;

use xPDO\Om\xPDOSimpleObject;

/**
 * Class LinguaLangs
 *
 * @property integer $active
 * @property string $local_name
 * @property string $lang_code
 * @property string $lcid_string
 * @property integer $lcid_dec
 * @property string $date_format_lite
 * @property string $date_format_full
 * @property integer $is_rtl
 * @property string $flag
 *
 * @property \LinguaSiteContent[] $SiteContent
 * @property \LinguaSiteTmplvarContentvalues[] $TmplvarContentvalues
 *
 * @package Lingua\Model
 */
class LinguaLangs extends xPDOSimpleObject
{
}
