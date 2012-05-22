<?php

if (!extension_loaded('gd') && !extension_loaded('gd2')) {
    throw new Exception('GD2 php extension not found');
}

using('Framework.Vendors.WideImage');