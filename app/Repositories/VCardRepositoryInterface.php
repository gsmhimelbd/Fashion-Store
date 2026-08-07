<?php
namespace App\Repositories;
use App\Models\VCard;
interface VCardRepositoryInterface { public function findPublishedBySlug(string $slug): VCard; public function findForUser(int $id, int $userId): VCard; }
