<?php

namespace Pingumask\Discord;

enum ButtonStyle: int
{
    case PRIMARY = 1; // Blurple
    case SECONDARY = 2; // Grey
    case SUCCESS = 3; // Green
    case DANGER = 4; // Red
    case LINK = 5; // Grey with link icon
}
