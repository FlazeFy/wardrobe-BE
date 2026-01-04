<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Question",
 *     type="object",
 *     required={"id", "question", "is_show", "created_at"},
 *
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary key for the question"),
 *     @OA\Property(property="question", type="string", maxLength=500, description="Question text"),
 *     @OA\Property(property="answer", type="string", maxLength=500, nullable=true, description="Answer to the question"),
 *     @OA\Property(property="is_show", type="boolean", description="Indicates whether the question is visible"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the question was created")
 * )
 */

class QuestionModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'question';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'question', 'answer', 'is_show', 'created_at', 'created_by'];

    public static function getFAQ(){
        return QuestionModel::select('question', 'answer', 'created_at')
            ->whereNotNull('answer')
            ->where('is_show',1)
            ->orderby('created_at','desc')
            ->limit(8)
            ->get();
    }

    public  static function getUnansweredQuestion(){
        $res = QuestionModel::select('question', 'created_at')
            ->whereNull('answer')
            ->orderby('created_at','desc')
            ->get();

        return count($res) > 0 ? $res : null;
    }
}
