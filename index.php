<?php

require_once __DIR__ . '/autoload.php';

use zzakharov\Maze\Game;

$game = new Game(__DIR__ . '/fields/complete1.png');

$player = $game->getPlayer();

while (!$game->checkWin()) {
    $aroundPlayer = $player->lookAround();

    switch (false) {
        case $aroundPlayer['Слева стена']:
            $direction = 'влево';
            break;
        case $aroundPlayer['Спереди стена']:
            $direction = 'вперед';
            break;
        case $aroundPlayer['Справа стена']:
            $direction = 'вправо';
            break;
        default:
            $direction = 'назад';
    }

    $player->move($direction);
}

$game->drawShortPath();

header('Content-Type: image/png');
exit($game->getImageBlob());
