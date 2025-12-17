<?php

namespace FlyingPress\Integrations;

class Plugins
{
  public static function init()
  {
    Plugins\MultiCurrency\WCML::init();
    Plugins\Marketing\PrettyLinks::init();
    Plugins\Optimization\SiteGround::init();
    Plugins\Optimization\Breeze::init();
    Plugins\Optimization\EWWW::init();
    Plugins\Optimization\ShortPixelAI::init();
    Plugins\Optimization\Perfmatters::init();
    Plugins\Optimization\NginxHelper::init();
    Plugins\PageBuilder\Elementor::init();
    Plugins\PageBuilder\BreakDance::init();
    Plugins\PageBuilder\Oxygen::init();
  }
}
