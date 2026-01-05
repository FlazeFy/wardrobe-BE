<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Helper
use App\Helpers\Generator;

/**
 * @OA\Schema(
 *     schema="Feedback",
 *     type="object",
 *     required={"id", "feedback_rate", "feedback_body", "created_at", "created_by"},
 *
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary key for the feedback"),
 *     @OA\Property(property="feedback_rate", type="integer", description="Rating value given by the user"),
 *     @OA\Property(property="feedback_body", type="string", maxLength=144, description="Feedback message or comment"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the feedback was created"),
 *     @OA\Property(property="created_by", type="string", maxLength=36, description="ID of the user who submitted the feedback")
 * )
 */

class FeedbackModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'feedback';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'feedback_rate', 'feedback_body', 'created_at', 'created_by'];

    public static function getTopFeedback(){
        return FeedbackModel::selectRaw('CAST(feedback_rate as UNSIGNED) as feedback_rate, feedback_body, feedback.created_at, username')
            ->join('users','users.id','=','feedback.created_by')
            ->orderby('feedback_rate','DESC')
            ->groupby('username')
            ->limit(4)
            ->get();
    }

    public static function getAll($paginate){
        return FeedbackModel::selectRaw('feedback.id, feedback_rate, feedback_body, feedback.created_at, users.username as created_by')
            ->join('users','users.id','=','feedback.created_by')
            ->orderby('feedback.created_at', 'DESC')
            ->paginate($paginate);
    }

    public static function createFeedback($data, $user_id){
        $data["id"] = Generator::getUUID();
        $data["created_at"] = date("Y-m-d H:i:s");
        $data["created_by"] = $user_id;

        return FeedbackModel::create($data);
    }
}
