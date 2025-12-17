<?php

namespace FlyingPress\Integrations;

class Themes
{
  public static function init()
  {
    Themes\BuddyBoss::init();
    Themes\Divi::init();
  }
}
