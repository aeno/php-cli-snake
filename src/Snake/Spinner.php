<?php

declare(strict_types=1);

namespace AlecRabbit\Snake;

use AlecRabbit\Snake\Contracts\Color;
use AlecRabbit\Snake\Contracts\SpinnerInterface;
use AlecRabbit\Snake\Core\Driver;

class Spinner implements SpinnerInterface
{
    private const CHARS = ['⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇'];
    private const COLORS = [
        196,
        196,
        202,
        202,
        208,
        208,
        214,
        214,
        220,
        220,
        226,
        226,
        190,
        190,
        154,
        154,
        118,
        118,
        82,
        82,
        46,
        46,
        47,
        47,
        48,
        48,
        49,
        49,
        50,
        50,
        51,
        51,
        45,
        45,
        39,
        39,
        33,
        33,
        27,
        27,
        56,
        56,
        57,
        57,
        93,
        93,
        129,
        129,
        165,
        165,
        201,
        201,
        200,
        200,
        199,
        199,
        198,
        198,
        197,
        197,
    ];

    /** @var Driver */
    private $driver;
    /** @var int */
    private $currentCharIdx = 0;
    /** @var int */
    private $currentColorIdx = 0;
    /** @var int */
    private $framesCount;
    /** @var int */
    private $colorCount;
    /** @var string|null */
    protected $message;
    /** @var int */
    protected $terminalCols;
    /** @var float */
    protected $lastFrameTimestamp = 0;

    public function __construct(int $colorLevel = Color::COLOR_256)
    {
        $this->driver = new Driver($colorLevel);
        $this->framesCount = count(self::CHARS);
        $this->colorCount = count(self::COLORS);
        $this->terminalCols = (int) (exec('tput cols') ?? 80);
    }

    public function spin(): void
    {
        $message = is_string($this->message) && !empty($this->message)
            ? ' ' . $this->message
            : '';

        $output =
            $this->driver->eraseSequence()
            . $this->driver->frameSequence(
                self::COLORS[$this->currentColorIdx],
                self::CHARS[$this->currentCharIdx]
            )
            . $message;

        $spaces = $this->terminalCols - mb_strlen($output);
        $output = $output . str_repeat(' ', max(0, $spaces));

        $this->driver->write(
            $output,
            "\r"
        );
        $this->update();
    }

    private function update(): void
    {
        /** @var float $now */
        $now = microtime(true);
        if ($now >= $this->lastFrameTimestamp + $this->interval()) {
            $this->lastFrameTimestamp = $now;

            if (++$this->currentCharIdx === $this->framesCount) {
                $this->currentCharIdx = 0;
            }
            if (++$this->currentColorIdx === $this->colorCount) {
                $this->currentColorIdx = 0;
            }
        }
    }

    /** @inheritDoc */
    public function interval(): float
    {
        return 0.1;
    }

    public function begin(): void
    {
        $this->driver->hideCursor();
    }

    public function end(): void
    {
        $this->erase();
        $this->driver->showCursor();
    }

    public function erase(): void
    {
        $this->driver->write(
            $this->driver->eraseSequence()
        );
    }

    public function useStdOut(): void
    {
        $this->driver->useStdOut();
    }

    /** @inheritDoc */
    public function setMessage(?string $message): void
    {
        if ($message === null) {
            $this->message = null;
            return;
        }

        $message = mb_substr($message, 0, $this->terminalCols - 10);
        $breakPosition = mb_strpos($message, "\n");

        if ($breakPosition !== false) {
            $message = mb_substr($message, 0, $breakPosition);
        }

        $this->message = trim($message);
    }
}
