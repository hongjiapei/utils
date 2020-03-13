<?php

if (!function_exists('is_plate')) {
    function is_plate($license)
    {
        if (empty($license)) {
            return false;
        }
        #匹配民用车牌和使馆车牌
        # 判断标准
        # 1，第一位为汉字省份缩写
        # 2，第二位为大写字母城市编码
        # 3，后面是5位仅含字母和数字的组合
        {
            $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤粵桂琼川贵云渝藏陕甘青宁新使]{1}[A-Z]{1}[0-9a-zA-Z]{5,6}$/u";
            preg_match($regular, $license, $match);
            if (isset($match[0])) {
                return true;
            }
        }

        #匹配特种车牌(挂,警,学,领,港,澳)
        #参考 https://wenku.baidu.com/view/4573909a964bcf84b9d57bc5.html
        {
            $regular = '/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤粵桂琼川贵云渝藏陕甘青宁新]{1}[1-9A-Z]{1}[0-9a-zA-Z]{4,5}[挂警学领港澳]{1}$/u';
            preg_match($regular, $license, $match);
            if (isset($match[0])) {
                return true;
            }
        }

        #匹配使馆车辆
        {
            $regular = "/[A-Z0-9]{5,6}使$/";
            preg_match($regular, $license, $match);
            if (isset($match[0])) {
                return true;
            }
        }

        #匹配武警车牌
        #参考 https://wenku.baidu.com/view/7fe0b333aaea998fcc220e48.html
        {
            $regular = '/^WJ[0-9]{5}/i';
            preg_match($regular, $license, $match);
            if (isset($match[0])) return true;

            $regular = '/^WJ[0-9]{4}[XBTSHJD]$/i';
            preg_match($regular, $license, $match);
            if (isset($match[0])) return true;

            $regular = '/^WJ[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[0-9]{5}$/u';
            preg_match($regular, $license, $match);
            if (isset($match[0])) return true;

            $regular = '/^WJ[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[0-9]{4}[XBTSHJD]$/u';
            preg_match($regular, $license, $match);
            if (isset($match[0])) return true;

            $regular = '/^(KM|KJ)[0-9]{4,5}$/i';
            preg_match($regular, $license, $match);
            if (isset($match[0])) return true;
        }


        #匹配新加坡
        #参考 http://auto.sina.com.cn/service/2013-05-03/18111149551.shtml
        {
            $regular = "/[A-Z0-9]{5,}$/";
            preg_match($regular, $license, $match);
            if (isset($match[0])) {
                return true;
            }
        }
        return false;
    }
}

