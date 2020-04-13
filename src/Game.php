<?php

namespace zzakharov\Maze;

class Game
{
    private \Imagick $imagickMaze;
    private CellFactory $cellFactory;
    private Player $player;
    private Cell $startCell;
    private Cell $endCell;
    private array $field = [];
    private int $widthCellPx;
    private int $heightCellPx;
    private string $startColorHex;
    private string $endColorHex;
    private string $pathColorHex;

    public function __construct(string $imagePath, array $options = [])
    {
        $defaultOptions = [
            'widthCellPx' => 10,
            'heightCellPx' => 10,
            'startColorHex' => 'ff0000',
            'endColorHex' => '0000ff',
            'pathColorHex' => '00ff00',
        ];

        foreach ($defaultOptions as $key => $value) {
            $this->$key = $options[$key] ?? $value;
        }

        $this->imagickMaze = new \Imagick($imagePath);
        $this->cellFactory = new CellFactory(
            $this->widthCellPx,
            $this->heightCellPx,
            $this->imagickMaze
        );

        $gamePointsCoordinates = $this->findMazePoints();

        $this->startCell = $this->getCellFromField(
            $gamePointsCoordinates['startPx']['x'] / $this->widthCellPx,
            $gamePointsCoordinates['startPx']['y'] / $this->heightCellPx
        );

        $this->endCell = $this->getCellFromField(
            $gamePointsCoordinates['endPx']['x'] / $this->widthCellPx,
            $gamePointsCoordinates['endPx']['y'] / $this->heightCellPx
        );

        $this->player = new Player($this, $this->startCell);
    }

    private function findMazePoints(): array
    {
        $pixelIterator = $this->imagickMaze->getPixelIterator();
        $startPxCoordinates = null;
        $endPxCoordinates = null;

        $searchColors = [
            'startPxCoordinates' => $this->startColorHex,
            'endPxCoordinates' => $this->endColorHex
        ];

        $searchColors = array_map(function ($hexColor) {
            list($r, $g, $b) = sscanf($hexColor, '%02x%02x%02x');
            return "srgb({$r},{$g},{$b})";
        }, $searchColors);

        foreach ($pixelIterator as $row => $columns) {
            foreach ($columns as $column => $pixel) {
                $pixelColor = $pixel->getColorAsString();

                if ($foundColor = array_search($pixelColor, $searchColors)) {
                    $$foundColor = [
                        'x' => $column,
                        'y' => $row
                    ];
                }

                if ($startPxCoordinates && $endPxCoordinates) {
                    return [
                        'startPx' => $startPxCoordinates,
                        'endPx' => $endPxCoordinates
                    ];
                }
            }

            $pixelIterator->syncIterator();
        }

        throw new \Exception('Ошибка поиска игровых точек');
    }

    public function getPlayer()
    {
        return $this->player;
    }

    public function getCellFromField(int $xCell, int $yCell): Cell
    {
        $xPx = $this->widthCellPx * $xCell;
        $yPx = $this->heightCellPx * $yCell;

        $fieldCell = $this->field[$xCell][$yCell] ??
        ($this->field[$xCell][$yCell] = $this->cellFactory->createCell($xPx, $yPx));

        return $fieldCell;
    }

    public function checkWin(): bool
    {
        if ($this->player->currentCell === $this->endCell) {
            $countSteps = count($this->player->path);
            return true;
        }

        return false;
    }

    public function getImageBlob(): string
    {
        return $this->imagickMaze->getImageBlob();
    }

    private function drawLine(Cell $cell, string $direction): void
    {
        $draw = new \ImagickDraw();
        $draw->setFillColor("#{$this->pathColorHex}");

        $centerCellXPx = $cell->xPx + $this->widthCellPx / 2;
        $centerCellYPx = $cell->yPx + $this->heightCellPx / 2;

        switch ($direction) {
            case 't':
                $endLineXPx = $centerCellXPx;
                $endLineYPx = $cell->yPx;
                break;
            case 'r':
                $endLineXPx = $cell->xPx + $this->widthCellPx;
                $endLineYPx = $centerCellYPx;
                break;
            case 'b':
                $endLineXPx = $centerCellXPx;
                $endLineYPx = $cell->yPx + $this->heightCellPx;
                break;
            case 'l':
                $endLineXPx = $cell->xPx;
                $endLineYPx = $centerCellYPx;
                break;
            default:
                throw new \Exception('Неверное значение направления движения');
        }

        $draw->line(
            $centerCellXPx,
            $centerCellYPx,
            $endLineXPx,
            $endLineYPx
        );

        $draw->line(
            $centerCellXPx - 1,
            $centerCellYPx - 1,
            $endLineXPx - 1,
            $endLineYPx - 1
        );

        $draw->rectangle(
            $centerCellXPx,
            $centerCellYPx,
            $centerCellXPx - 1,
            $centerCellYPx - 1
        );

        $gamePoints = [
            "#{$this->startColorHex}" => $this->startCell,
            "#{$this->endColorHex}" => $this->endCell
        ];

        foreach ($gamePoints as $color => $cell) {
            $draw->setFillColor($color);

            $topLeftX = $cell->xPx + 2;
            $topLeftY = $cell->yPx + 2;

            $bottomRightX = $cell->xPx + $this->widthCellPx - 2;
            $bottomRightY = $cell->yPx + $this->heightCellPx - 2;

            $draw->rectangle($topLeftX, $topLeftY, $bottomRightX, $bottomRightY);
        }

        $this->imagickMaze->drawImage($draw);
    }

    public function drawPath(array $path = null): void
    {
        $path = $path ?? $this->player->path;

        foreach ($path as $step) {
            $diffX = $step->toCell->x - $step->fromCell->x;
            $diffY = $step->toCell->y - $step->fromCell->y;

            switch ([$diffX, $diffY]) {
                case [0, -1]:
                    $direction = 't';
                    $oppositeDirection = 'b';
                    break;
                case [1, 0]:
                    $direction = 'r';
                    $oppositeDirection = 'l';
                    break;
                case [0, 1]:
                    $direction = 'b';
                    $oppositeDirection = 't';
                    break;
                case [-1, 0]:
                    $direction = 'l';
                    $oppositeDirection = 'r';
                    break;
                default:
                    throw new \Exception('Ошибка при передвижении');
            }

            $this->drawLine($step->fromCell, $direction);
            $this->drawLine($step->toCell, $oppositeDirection);
        }
    }

    public function drawShortPath(): void
    {
        $path = $this->player->path;
        $pathLength = count($path);
        $shortPath = [];

        for ($indexStep = 0; $indexStep < $pathLength; $indexStep++) {
            $step = $path[$indexStep];
            $from = $step->fromCell;
            $nextSteps = array_slice($path, $indexStep + 1);
            $reverseNextSteps = array_reverse($nextSteps, true);

            foreach ($reverseNextSteps as $indexNextStep => $nextStep) {
                $nextFrom = $nextStep->fromCell;

                if ($nextFrom === $from) {
                    $indexStep = $indexNextStep + $indexStep + 1;
                }
            }

            $shortPath[] = $path[$indexStep];
        }

        $this->drawPath($shortPath);
    }
}
