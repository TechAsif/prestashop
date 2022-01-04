<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class AfSlider
{
    public function assignParamsForNumericSliders(&$params)
    {
        foreach ($params['numeric_slider_values'] as $key => $grouped_values) {
            foreach ($grouped_values as $id_group => $values) {
                if (isset($params['sliders'][$key][$id_group])) {
                    $slider = $params['sliders'][$key][$id_group];
                    if ($this->isTriggered($slider)) {
                        $values = is_array($values) ? $values : explode(',', $values);
                        $ids = $params['available_options'][$key][$id_group];
                        $ids = is_array($ids) ? $ids : explode(',', $ids);
                        $numeric_values = array_combine($ids, $values);
                        foreach ($numeric_values as $id => $number) {
                            $possible_range = ExtendedTools::explodeRangeValue($number);
                            if ($possible_range[1] >= $slider['from'] && $possible_range[0] <= $slider['to']) {
                                $params['filters'][$key][$id_group][] = $id;
                            }
                        }
                        if (empty($params['filters'][$key][$id_group])) {
                            // no available options within selected range
                            $params['filters'][$key][$id_group][] = 'none';
                        }
                    }
                }
            }
        }
    }

    public function isTriggered($slider_data)
    {
        $values = $this->fillValues($slider_data);
        return $values['from'] > $values['min'] || $values['to'] < $values['max'];
    }

    public function fillValues($slider_data)
    {
        if (isset($slider_data['selected_values'][0][0]) && isset($slider_data['selected_values'][0][1])) {
            $slider_data['from'] = $slider_data['selected_values'][0][0];
            $slider_data['to'] = $slider_data['selected_values'][0][1];
        }
        $min = isset($slider_data['min']) ? $slider_data['min'] : 0;
        $max = isset($slider_data['max']) ? $slider_data['max'] : 10000000000;
        $from = isset($slider_data['from']) && $slider_data['from'] > $min ? $slider_data['from'] : $min;
        $to = isset($slider_data['to']) && $slider_data['to'] < $max ? $slider_data['to'] : $max;
        return array('min' => $min, 'max' => $max, 'from' => $from, 'to' => $to);
    }

    public function setExtensions(&$filter, $is_17)
    {
        if ($filter['first_char'] == 'p') {
            $this->context = Context::getContext();
            $currency = $this->context->currency;
            if ($is_17) {
                $this->setCurrencyExtensions($currency);
            }
            $filter['prefix'] = $currency->prefix;
            $filter['suffix'] = $currency->suffix;
        } else {
            $filter['prefix'] = ltrim($filter['slider_prefix'].' ');
            $filter['suffix'] = rtrim(' '.$filter['slider_suffix']);
        }
    }

    public function setCurrencyExtensions(&$currency)
    {
        if (!$currency->prefix && !$currency->suffix) {
            $format = $currency->format;
            if (!$format && method_exists($this->context->controller, 'getContainer')) {
                $format = $this->context->controller->getContainer()->
                    get(Tools::SERVICE_LOCALE_REPOSITORY)->
                    getLocale($this->context->language->getLocale())->
                    getPriceSpecification($currency->iso_code)->toArray()['positivePattern'];
            }
            if (Tools::substr($format, 0, 1) === '¤') {
                $currency->prefix = $currency->sign;
                if (urlencode(Tools::substr($format, 1, 2)) === '%C2%A0') {
                    $currency->prefix .= ' ';
                }
            } else {
                $currency->suffix = $currency->sign;
                if (urlencode(Tools::substr($format, -2, -1)) === '%C2%A0') {
                    $currency->suffix = ' '.$currency->suffix;
                }
            }
        }
    }
}
