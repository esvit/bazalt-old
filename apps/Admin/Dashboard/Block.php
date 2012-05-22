<?php

abstract class Admin_Dashboard_Block
{
    public $id;

    public $html;

    abstract function getTitle();

    abstract function getContent();
}