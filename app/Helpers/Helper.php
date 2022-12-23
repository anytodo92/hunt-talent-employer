<?php

if (!function_exists('g_enum')) {
    /**
     * Enumeration
     * ID値から表示テキストを取得する。IDを指定しないと全体データを取得する。
     * @param string $enumID
     * @param integer | string | null $enumValue
     * @return mixed | array
     */
    function g_enum($enumID, $value = null)
    {
        $enumerations = config('constants');

        $enumArray = array();
        if (isset($enumerations[$enumID]))
            $enumArray = $enumerations[$enumID];

        if (!is_array($enumArray))
            $enumArray = array();

        // 表示テキスト
        $result = array();
        if (null !== $value) {
            if (strpos($value, ',') !== false) {
                $values = explode(',', $value);
            } else {
                $values = array($value);
            }
            foreach ($values as $value) {
                if (isset($enumArray[$value])) {
                    if (is_array($enumArray[$value]) && count($enumArray[$value]) > 0) {
                        return $enumArray[$value];
                    }
                    $result[] = $enumArray[$value];
                }
            }
            $result = implode('<br>', $result);
            return $result;
        }

        return $enumArray;
    }
}

if (!function_exists('g_age')) {

	function g_age($date) {
		return (new \Carbon\Carbon($date))->age;
	}
}

if (!function_exists('g_formatBirthday')) {
    /**
     *
     *
     * @param string $string
     * @return string
     */
    function g_formatBirthday($dateString)
    {
        $date = strtotime($dateString);
        return date('Y年n月j日', $date) . ' (' . g_age($date) . '歳)';
    }
}

if (!function_exists('g_nextPhase')) {
    /**
     *
     *
     * @param string $string
     * @return string
     */
    function g_nextPhase($flow, $current)
    {
        $steps = explode(",", $flow);
        for ($i=0; $i<count($steps); $i++) {
            if ($steps[$i] == $current) {
                if ($i == count($steps)-1) {
                    return null;
                }
                else {
                    return $steps[$i+1];
                }
            }
        }
        return null;
    }
}

if (!function_exists('g_grayLogo')) {
    /**
     *
     *
     * @param int $sexType
     * @return string
     */
    function g_grayLogo($sexType)
    {
        $url = '';
        switch ($sexType) {
            case 1: //男生
                $url = '/assets/static/images/avatar/avatar1.png';
                break;
            case 2: //女性
                $url = '/assets/static/images/avatar/avatar2.png';
                break;
            case 3: //不問
                $url = '/assets/static/images/avatar/avatar3.png';
                break;
        }
        return $url;
    }
}
if (!function_exists('g_dateFormat')) {
    /**
     *
     *
     * @param string $string
     * @return string
     */
    function g_dateFormat($dateString)
    {
        $date = strtotime($dateString);
        return date('Y.m.d H:i', $date);
    }
}

