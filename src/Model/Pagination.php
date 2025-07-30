<?php

namespace App\Model;

class Pagination
{
    private int $currentPage;
    private int $totalPages;
    private int $totalCount;
    private int $limit;
    private int $offset;
    private bool $hasNext;
    private bool $hasPrev;

    public function __construct(
        int $currentPage,
        int $totalPages,
        int $totalCount,
        int $limit,
        int $offset,
        bool $hasNext,
        bool $hasPrev
    ) {
        $this->currentPage = $currentPage;
        $this->totalPages = $totalPages;
        $this->totalCount = $totalCount;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->hasNext = $hasNext;
        $this->hasPrev = $hasPrev;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function hasNext(): bool
    {
        return $this->hasNext;
    }

    public function hasPrev(): bool
    {
        return $this->hasPrev;
    }

    public function toArray(): array
    {
        return [
            'current_page' => $this->currentPage,
            'total_pages' => $this->totalPages,
            'total_count' => $this->totalCount,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'has_next' => $this->hasNext,
            'has_prev' => $this->hasPrev
        ];
    }

    public static function create(int $page, int $limit, int $totalCount): self
    {
        $totalPages = ceil($totalCount / $limit);
        $offset = ($page - 1) * $limit;
        
        return new self(
            $page,
            $totalPages,
            $totalCount,
            $limit,
            $offset,
            $page < $totalPages,
            $page > 1
        );
    }
} 