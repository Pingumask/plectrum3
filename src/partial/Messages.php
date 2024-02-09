<?php

namespace Pingumask\Plectrum\Partial;

abstract class Message
{
    const FLAG_SUPPRESS_EMBEDS = 1 << 2;
    const FLAG_EPHEMERAL = 1 << 6;
}
