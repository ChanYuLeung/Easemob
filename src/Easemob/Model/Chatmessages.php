<?php

namespace Easemob\Model;

/**
 * @Synopsis  聊天记录 http://www.easemob.com/docs/rest/chatmessage/
 */
class Chatmessages extends BaseModel{

    /**
     * @Synopsis  获取某个时间段内的消息
     *
     * @Param $from 开始的时间戳
     * @Param $to 结束的时间戳
     *
     * @Returns   
     */
    public function getByTime($from,$to){
        return $this->easemob->ApiGet('/chatmessages/?ql=select+*+where+timestamp%3C'.$to.'+and+timestamp%3E'.$from);
    }

    /**
     * @Synopsis  分页获取数据
     *
     * @Param $limit 每一页条目数
     * @Param $cursor 游标
     *
     * @Returns   
     */
    public function getByPage($limit=20,$cursor=''){
        if(empty($cursor)){
            return $this->easemob->ApiGet('/chatmessages/?limit='.$limit);
        }else{
            return $this->easemob->ApiGet('/chatmessages/?limit='.$limit.'&cursor='.$cursor);
        }
    }

    /**
     * @Synopsis 获取一个IM用户的未读消息数 
     *
     * @Param $username
     *
     * @Returns   
     */
    public function getOfflineMsgCount($username){
        return $this->easemob->ApiGet('/users/'.$username.'/offline_msg_count');
    }

}
?>
