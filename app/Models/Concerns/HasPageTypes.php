<?php

namespace App\Models\Concerns;

trait HasPageTypes
{
    public function isPdfWorksheet(): bool
    {
        return $this->type === 'pdf_worksheet';
    }

    public function isInteractive(): bool
    {
        return $this->type === 'interactive';
    }

    public function isFreeWrite(): bool
    {
        return $this->type === 'free_write';
    }

    public function isReading(): bool
    {
        return in_array($this->type, ['reading_comprehension', 'recitation'], true);
    }

    public function isOral(): bool
    {
        return $this->type === 'oral_recording';
    }

    public function isRichDocument(): bool
    {
        return $this->type === 'rich_document';
    }

    public function isMathScroll(): bool
    {
        return $this->type === 'math_scroll';
    }

    public function isSubjectWorkspace(): bool
    {
        return $this->isReading() || $this->isOral() || $this->isRichDocument() || $this->isMathScroll();
    }

    public function needsCanvas(): bool
    {
        return in_array($this->type, ['pdf_worksheet', 'free_write', 'rich_document', 'math_scroll'], true);
    }
}
