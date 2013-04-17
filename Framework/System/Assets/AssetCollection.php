<?php

namespace Framework\System\Assets;

class AssetCollection
{
    const ASSET_TYPE_CSS = 'css';

    const ASSET_TYPE_JS = 'js';

    const NO_CONDITION = 'no_condition';

    /**
     * According to the conditional comment this is IE
     */
    const IE = 'if IE';

    /**
     * According to the conditional comment this is IE 6
     */
    const IE6 = 'if IE 6';

    /**
     * According to the conditional comment this is IE 7
     */
    const IE7 = 'if IE 7';

    /**
     * According to the conditional comment this is IE 8
     */
    const IE8 = 'if IE 8';

    /**
     * According to the conditional comment this is IE 9
     */
    const IE9 = 'if IE 9';

    /**
     * According to the conditional comment this is IE 8 or higher
     */
    const IE_GTE_8 = 'if gte IE 8';

    /**
     * According to the conditional comment this is IE lower than 9
     */
    const IE_LT_9 = 'if lt IE 9';

    /**
     * According to the conditional comment this is IE lower or equal to 7
     */
    const IE_LTE_7 = 'if lte IE 7';

    /**
     * According to the conditional comment this is IE greater than 6
     */
    const IE_GT_6 = 'if gt IE 6';

    /**
     * According to the conditional comment this is not IE
     */
    const NOT_IE = 'if !IE';

    protected $type = null;

    protected $condition = null;

    /**
     * Create new scope
     *
     * @param string $type
     * @param string $condition
     *
     * @return AssetCollection
     */
    public static function create($type = self::ASSET_TYPE_CSS, $condition = self::NO_CONDITION)
    {
        $collection = new AssetCollection($type, $condition);
        return $collection;
    }

    protected function __construct($type = self::ASSET_TYPE_CSS, $condition = self::NO_CONDITION)
    {
        if ($type != self::ASSET_TYPE_CSS && $type != self::ASSET_TYPE_JS) {
            throw \Exception('Invalid asset type');
        }
        $this->type = $type;
        $this->condition = $condition;
    }
}