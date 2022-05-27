<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Article;
use App\Repositories\ArticleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ArticleRepository implements ArticleRepositoryInterface
{
    public function __construct(private Article $model)
    {
        //
    }

    public function create(int $keywordId, ?string $url = null, ?string $content = null): Article
    {
        $model = $this->model->newInstance();

        $model->keyword_id = $keywordId;
        $model->url = $url;
        $model->content = $content;

        $model->save();

        return $model;
    }

    public function findByUrl(string $url): array|Collection
    {
        return $this->model->where(['url' => $url])->get();
    }

    public function findForKeyword(int $keywordId): array|Collection
    {
        return $this->model->where([
            'keyword_id' => $keywordId,
        ])->get();
    }
}
