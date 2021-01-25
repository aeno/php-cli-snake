<?php

declare(strict_types=1);

namespace AlecRabbit\Snake\Contracts;

interface SpinnerInterface
{
    public function spin(): void;

    /**
     * Returns spinner refresh interval in seconds
     * @return float
     */
    public function interval(): float;

    public function begin(): void;

    public function end(): void;

    public function erase(): void;

    /**
     * Switch to STDOUT instead of STDERR
     */
    public function useStdOut(): void;

    /**
     * Set a status message to be displayed along the spinner.
     * Set to null to show no status message.
     *
     * @param string|null $message
     */
    public function setMessage(?string $message): void;
}
