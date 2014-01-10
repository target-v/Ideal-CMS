<?php
namespace Ideal\Field\DateSet;

use Ideal\Field\Date;

class Controller extends Date\Controller
{
    protected static $instance;

    public function getValue()
    {
        $value = parent::getValue();
        if ($value == '') {
            $value = time();
        }
        return $value;
    }
}
