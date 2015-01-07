<?php
namespace Easemob\Model;
/**
 * @Synopsis  图片语音文件上传、下载  http://www.easemob.com/docs/rest/files/
 */
class Files extends BaseModel {
    /**
     * @Synopsis  上传语音图片
     *
     * @Param $filepath 图片位置
     *
     * @Returns   
     */
    public function upload($filepath) {
        return $this->easemob->ApiUpload('/chatfiles',$filepath);
    }

    /**
     * @Synopsis  下载图片,语音文件
     *
     * @Param $uuid
     * @Param $shareSecret
     * @Param $isThumbnail 是否是缩略图
     *
     * @Returns   
     */
    public function download($uuid,$shareSecret,$isThumbnail = False){
        return $this->easemob->ApiDownload('/chatfiles/'.$uuid,$shareSecret,True,$isThumbnail);
    }

}
?>
