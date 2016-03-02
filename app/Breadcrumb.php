<?php

/**
 * This file is part of eghojansu/Fatfree-bootstrap
 *
 * @author Eko Kurniawan <ekokurniawanbs@gmail.com>
 */

/**
 * Bootstrap breadcrumb
 */
class Breadcrumb
{
    protected $firstLabel;
    protected $firstTarget;
    protected $firstIcon;

    protected $childs = [];

    /**
     * Set breadcrumb root
     * @param string $label
     * @param string $target url
     * @param string $icon
     * @return object $this
     */
    public function setRoot($label, $target, $icon = null)
    {
        $this->firstLabel  = $label;
        $this->firstTarget = $target;
        $this->firstIcon   = $icon;

        return $this;
    }

    /**
     * Append breadcrumb
     * @param  string $label
     * @param  string $target url
     * @return object $this
     */
    public function append($label, $target = '#')
    {
        array_push($this->childs, [$target, $label]);

        return $this;
    }

    /**
     * Prepend breadcrumb
     * @param  string $label
     * @param  string $target url
     * @return object $this
     */
    public function prepend($label, $target = '#')
    {
        array_unshift($this->childs, [$target, $label]);

        return $this;
    }

    /**
     * Render breadcrumb
     * @return string bootstrap breadcrumb
     */
    public function render()
    {
        $childs = $this->childs;
        array_unshift($childs, [$this->firstTarget, ($this->firstIcon?'<i style="color: #777" class="fa fa-'.$this->firstIcon.'"></i> ':'').
            $this->firstLabel]);
        $last   = array_pop($childs);
        $list   = '';
        foreach ($childs as $key => $value) {
            $list .= '<li><a href="'.$value[0].'">'.$value[1].'</a></li>';
        }

        return '<ol class="breadcrumb">'.
            $list.
            '<li class="active">'.$last[1].'</li>'.
            '</ol>';
    }
}