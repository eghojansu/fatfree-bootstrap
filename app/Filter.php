<?php

// TODO: add email, ip address validation and other needed validation
/**
 * Filter class
 *
 * @author Eko Kurniawan <fkurniawan@outlook.com>
 */
final class Filter extends Prefab
{
    public function filterFilter(array $fieldFilters)
    {
        $fieldPattern = "/^\s*(?:(?<name>[\w]+)\s+as\s+(?<alias>[\w ]+))\s*:/i";
        $filterPattern = "/(\\[)((?:(?!\\1)[^\\\\]|(?:\\\\)*\\[^\\])*)(?:\\])/";
        $argsPattern = "/(?<var>\S+?)\s*=\s*(?<val>.+)\s*(?>;\s*\n*)/U";

        $validatedFilters = [];

        foreach ($fieldFilters as $fieldFilter) {
            if (!preg_match($fieldPattern, $fieldFilter, $field))
                user_error('Invalid filter', E_USER_ERROR);

            if (!preg_match_all($filterPattern, $fieldFilter, $filters, PREG_SET_ORDER))
                continue;

            $field = array_intersect_key($field, array_flip(array_filter(array_keys($field), 'is_string')));
            $validFilter = [];
            foreach ($filters as $filter) {
                $x = explode(':', $filter[2], 2);
                $filter = trim(array_shift($x));
                $args = [];
                if (isset($x[0]) && preg_match_all($argsPattern, rtrim($x[0], ';').';', $matchArgs, PREG_SET_ORDER))
                    foreach ($matchArgs as $arg) {
                        if (in_array($arg['val'], ['true','false']))
                            $arg['val'] = $arg['val']==='true';
                        if (is_numeric($arg['var']))
                            $args[] = $arg['val'];
                        else
                            $args[$arg['var']] = $arg['val'];
                    }

                $validFilter[$filter] = $args;
            }

            $validatedFilters[$field['name']] = $field + ['filter'=>$validFilter];
        }

        return $validatedFilters;
    }

    public function required($val, array $opt = [])
    {
        $opt += [
            'negate'=>false,
        ];
        $result = isset($val);

        return (bool) ($opt['negate']?!$result:$result);
    }

    public function alpha($val, array $opt = [])
    {
        $opt += [
            'negate'=>false,
            'space'=>false,
            'max'=>0,
            'min'=>0,
        ];

        $result = preg_match('/^[[:alpha:]'.($opt['space']?' ':'').']+$/', $val);
        if ($opt['max'])
            $result &= (strlen($val)<=$opt['max']);
        if ($opt['min'])
            $result &= (strlen($val)>=$opt['min']);

        return (bool) ($opt['negate']?!$result:$result);
    }

    public function alnum($val, array $opt = [])
    {
        $opt += [
            'negate'=>false,
            'space'=>false,
            'max'=>0,
            'min'=>0,
        ];

        $result = preg_match('/^[[:alnum:]'.($opt['space']?' ':'').']+$/', $val);
        if ($opt['max'])
            $result &= (strlen($val)<=$opt['max']);
        if ($opt['min'])
            $result &= (strlen($val)>=$opt['min']);

        return (bool) ($opt['negate']?!$result:$result);
    }

    public function numeric($val, array $opt = [])
    {
        $opt += [
            'negate'=>false,
            'space'=>false,
            'commaperiod'=>false,
            'max'=>0,
            'min'=>0,
        ];

        $result = preg_match('/^[[:digit:]'.($opt['space']?' ':'').($opt['commaperiod']?',\\.':'').']+$/', $val);
        if ($opt['max'])
            $result &= ($val<=$opt['max']);
        if ($opt['min'])
            $result &= ($val>=$opt['min']);

        return (bool) ($opt['negate']?!$result:$result);
    }

    public function dateSQL($val, array $opt = [])
    {
        $opt += [
            'convert'=>false,
            'delim'=>'/',
        ];

        $result = (bool) (preg_match('/^(\d{4})\-(\d{2})\-(\d{2})$/i', $val, $match));
        if ($result && $opt['convert'])
            $result = $match[3].$opt['delim'].$match[2].$opt['delim'].$match[1];

        return $result;
    }

    public function dateIndo($val, array $opt = [])
    {
        $opt += [
            'convert'=>false,
            'delim'=>'-',
        ];

        $result = (bool) (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/i', $val, $match));
        if ($result && $opt['convert'])
            $result = $match[3].$opt['delim'].$match[2].$opt['delim'].$match[1];

        return $result;
    }

    public function inArray($val, array $opt = [])
    {
        $opt += [
            'negate'=>false,
            'range'=>[],
        ];
        $result = in_array($val, $opt['range']);

        return (bool) ($opt['negate']?!$result:$result);
    }

    public function match($val, array $opt = [])
    {
        $opt += [
            'negate'=>false,
            'value'=>'',
            'pattern'=>'',
        ];
        $result = ($opt['value']?$opt['value']===$val:preg_match($opt['pattern'], $val));

        return $opt['negate']?!$result:$result;
    }

    public function inMap($val, array $opt = [])
    {
        $opt += [
            'negate'=>false,
            'class'=>'', // DB\SQL\Mapper
            'field'=>'',
        ];
        $map = new $opt['class'];
        $map->load([$opt['field'].'=?', $val],['limit'=>1]);
        $result = $map->valid();

        return (bool) ($opt['negate']?!$result:$result);
    }

    public function uniqueMap($val, array $opt = [])
    {
        $opt += [
            'negate'=>false,
            'instance'=>'', // DB\SQL\Mapper
            'field'=>'',
            'field_id'=>'',
        ];
        $opt['field_id'] || $opt['field_id'] = $opt['field'];
        $clone = clone $opt['instance'];
        $clone->load([$opt['field'].'=? and '.$opt['field'].'<>?', $val,$opt['instance'][$opt['field']]],['limit'=>1]);

        $result = ($clone->dry() || $opt['instance'][$opt['field_id']]===$clone[$opt['field_id']]);

        return (bool) ($opt['negate']?!$result:$result);
    }
}