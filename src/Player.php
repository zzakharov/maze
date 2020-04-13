<?php

namespace zzakharov\Maze;

class Player
{
    private int $visionAngle = 0;
    private array $path = [];
    private Cell $currentCell;
    private Game $game;

    public function __construct(Game $game, Cell $startCell)
    {
        $this->game = $game;
        $this->currentCell = $startCell;
    }

    public function move(string $direction): bool
    {
        $coordValues = [-1, 0, 1, 0];
        $coordValuesLength = count($coordValues);

        switch ($direction) {
            case 'вперед':
                $diffDirection = 3;
                break;
            case 'вправо':
                $diffDirection = 2;
                break;
            case 'назад':
                $diffDirection = 1;
                break;
            case 'влево':
                $diffDirection = 0;
                break;
            default:
                throw new \Exception('Неверное направление движения');
        }

        $xIndex = ($diffDirection - $this->visionAngle / 90) % $coordValuesLength;

        if ($xIndex < 0) {
            $xIndex = abs($coordValuesLength + $xIndex) % $coordValuesLength;
        }

        $yIndex = ($xIndex + 1) % $coordValuesLength;

        $diffCoords = [
            'x' => $coordValues[$xIndex],
            'y' => $coordValues[$yIndex],
        ];

        $newAnglesCoordRelative = [
            -1 => [270],
            1 => [90],
            0 => [
                -1 => 0,
                1 => 180
            ]
        ];

        $this->visionAngle = $newAnglesCoordRelative[$diffCoords['x']][$diffCoords['y']];

        $toCellCoords['x'] =  ($this->currentCell->x) + $diffCoords['x'];
        $toCellCoords['y'] =  ($this->currentCell->y) + $diffCoords['y'];

        $toCell = $this->game->getCellFromField($toCellCoords['x'], $toCellCoords['y']);

        $playerStep = new Step($this->currentCell, $toCell);

        if ($playerStep->isPossible) {
            $this->path[] = $playerStep;
            $this->currentCell = $toCell;
        }

        return $playerStep->isPossible;
    }

    public function lookAround(): array
    {
        $cellWalls = [
            $this->currentCell->borderTopExist,
            $this->currentCell->borderRightExist,
            $this->currentCell->borderBottomExist,
            $this->currentCell->borderLeftExist
        ];
        $cellWallsLength = count($cellWalls);

        $looks = [
            'Спереди стена',
            'Справа стена',
            'Сзади стена',
            'Слева стена',
        ];

        $wallsAround = [];

        $frontWallIndex = $this->visionAngle / 90;
        $maxTurnIndex = $frontWallIndex + $cellWallsLength - 1;

        for ($i = $frontWallIndex; $i <= $maxTurnIndex; $i++) {
            $wallsAround[array_shift($looks)] = $cellWalls[$i % $cellWallsLength];
        }

        return $wallsAround;
    }

    public function __get($property)
    {
        return $this->$property;
    }
}
