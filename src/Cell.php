<?php

namespace zzakharov\Maze;

class Cell
{
    private int $x;
    private int $y;
    private int $xPx;
    private int $yPx;
    private bool $borderTopExist = false;
    private bool $borderRightExist = false;
    private bool $borderBottomExist = false;
    private bool $borderLeftExist = false;

    public function __construct(array $fieldCoordinates, array $pxCoordinates, array $walls)
    {
        foreach ($walls as $wall => $isExist) {
            if (property_exists($this, $wall)) {
                $this->$wall = $isExist;
            }
        }

        list(
            'x' => $this->x,
            'y' => $this->y
        ) = $fieldCoordinates;

        list(
            'x' => $this->xPx,
            'y' => $this->yPx
        ) = $pxCoordinates;
    }

    public function __get($property)
    {
        return $this->$property;
    }
}
