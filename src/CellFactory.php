<?php

namespace zzakharov\Maze;

class CellFactory
{
    private int $widthCellPx;
    private int $heightCellPx;
    private \Imagick $imagickMaze;

    public function __construct(int $widthCellPx, int $heightCellPx, \Imagick $imagickMaze)
    {
        $this->widthCellPx = $widthCellPx;
        $this->heightCellPx = $heightCellPx;
        $this->imagickMaze = $imagickMaze;
    }

    public function createCell(int $xPx, int $yPx): Cell
    {
        $fieldCoordinates = $this->convertPxToFieldCoordinates($xPx, $yPx);
        $cellCoordinatesPx = $this->roundPxCoordinates($xPx, $yPx);

        $walls = $this->detectWalls(
            $cellCoordinatesPx['x'],
            $cellCoordinatesPx['y']
        );

        return new Cell(
            $fieldCoordinates,
            $cellCoordinatesPx,
            $walls
        );
    }

    private function roundPxCoordinates(int $xPx, int $yPx): array
    {
        $fieldCoordinates = $this->convertPxToFieldCoordinates($xPx, $yPx);

        $x = $fieldCoordinates['x'] * $this->widthCellPx;
        $y = $fieldCoordinates['y'] * $this->heightCellPx;

        return compact('x', 'y');
    }


    private function convertPxToFieldCoordinates(int $xPx, int $yPx): array
    {
        $x = intval(floor($xPx / $this->widthCellPx));
        $y = intval(floor($yPx / $this->heightCellPx));

        return compact('x', 'y');
    }

    private function detectWalls(int $xPx, int $yPx): array
    {
        $borderTopExist = false;
        $borderRightExist = false;
        $borderBottomExist = false;
        $borderLeftExist = false;

        $clonedPart = $this->imagickMaze->clone();

        $clonedPart->cropImage(
            $this->widthCellPx + 1,
            $this->heightCellPx + 1,
            $xPx,
            $yPx
        );

        $bordersCheckCoordinates = [
            'borderTop' => [1, 0],
            'borderLeft' => [0, 1],
            'borderBottom' => [
                1,
                $this->heightCellPx + 2
            ],
            'borderRight' => [
                $this->widthCellPx + 2,
                1
            ]
        ];

        foreach ($bordersCheckCoordinates as $border => $coordinates) {
            $borderPixelRgb = $clonedPart->exportImagePixels(
                $coordinates[0],
                $coordinates[1],
                1,
                1,
                'RGB',
                \Imagick::PIXEL_CHAR
            );

            if ($borderPixelRgb === [0, 0, 0]) {
                $borderVariable = "{$border}Exist";
                $$borderVariable = true;
            }
        }

        return compact(
            'borderTopExist',
            'borderRightExist',
            'borderBottomExist',
            'borderLeftExist'
        );
    }
}
