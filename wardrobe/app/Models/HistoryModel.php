<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Helper
use App\Helpers\Generator;

/**
 * @OA\Schema(
 *     schema="History",
 *     type="object",
 *     required={"id", "history_type", "history_context", "created_at", "created_by"},
 *
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary key for the history record"),
 *     @OA\Property(property="history_type", type="string", maxLength=36, description="Type or category of the history event"),
 *     @OA\Property(property="history_context", type="string", maxLength=255, description="Context or description of the history event"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the history record was created"),
 *     @OA\Property(property="created_by", type="string", maxLength=36, description="ID of the user who created the history record")
 * )
 */

class HistoryModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'history';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'history_type', 'history_context', 'created_at', 'created_by'];

    public static function getAll($user_id){
        return HistoryModel::where('created_by',$user_id)->orderby('created_at', 'DESC')->paginate(14);
    }

    public static function getHistoryExport($user_id){
        return HistoryModel::select('history_type','history_context','created_at')
            ->where('created_by',$user_id)
            ->orderby('created_at', 'DESC')
            ->get();
    }

    public static function createHistory($data, $user_id){
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $user_id;
        $data['id'] = Generator::getUUID();
            
        return HistoryModel::create($data);
    }

    public static function deleteHistoryForLastNDays($days){
        return HistoryModel::whereDate('created_at', '<', Carbon::now()->subDays($days))->delete();
    }
}
