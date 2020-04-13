<?php

namespace zzakharov\Maze;

class Step
{
    private Cell $fromCell;
    private Cell $toCell;
    private bool $isPossible = false;

    public function __construct(Cell $fromCell, Cell $toCell)
    {
        $this->fromCell = $fromCell;
        $this->toCell = $toCell;

        $xCoordinatesDiff = $toCell->x - $fromCell->x;
        $yCoordinatesDiff = $toCell->y - $fromCell->y;

        switch (true) {
            case $xCoordinatesDiff == 1:
                $checkWall = 'borderRightExist';
                break;
            case $xCoordinatesDiff == -1:
                $checkWall = 'borderLeftExist';
                break;
            case $yCoordinatesDiff == 1:
                $checkWall = 'borderBottomExist';
                break;
            case $yCoordinatesDiff == -1:
                $checkWall = 'borderTopExist';
                break;
        }

        if (!empty($checkWall)) {
            $this->isPossible = !$fromCell->$checkWall;
        }
    }

    public function __get($property)
    {
        return $this->$property;
    }
}
