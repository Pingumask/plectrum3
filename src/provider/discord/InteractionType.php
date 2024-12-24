<?php

namespace Pingumask\Discord;

enum InteractionType: int
{
    case INTERACTION_TYPE_PING = 1;
    case INTERACTION_TYPE_APPLICATION_COMMAND = 2;
    case INTERACTION_TYPE_MESSAGE_COMPONENT = 3;
    case INTERACTION_TYPE_APPLICATION_COMMAND_AUTOCOMPLETE = 4;
    case INTERACTION_TYPE_MODAL_SUBMIT = 5;
}