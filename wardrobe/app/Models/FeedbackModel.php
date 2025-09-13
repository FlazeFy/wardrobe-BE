<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public static function getAll(){
        return FeedbackModel::selectRaw('feedback.id, feedback_rate, feedback_body, feedback.created_at, users.username as created_by')
            ->join('users','users.id','=','feedback.created_by')
            ->orderby('feedback.created_at', 'DESC')
            ->paginate(14);
    }
}
