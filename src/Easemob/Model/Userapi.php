<?php

namespace Easemob\Model;

/**
 * @Synopsis  用户体系集成  http://www.easemob.com/docs/rest/userapi/
 */
class Userapi extends BaseModel {

    /**
     * @Synopsis  注册单个用户(注册IM用户[单个],授权注册)
     *
     * @Param $username
     * @Param $password
     * @Param $nickname
     *
     * @Returns   
     */
    public function register($username,$password,$nickname) {
        $params = array(
            'username' => $username,
            'password' => $password,
            'nickname' => $nickname 
        );
        return $this->easemob->ApiPost('/users',array($params));
    }

    /**
     * @Synopsis  获取IM用户[单个]
     *
     * @Param $username
     *
     * @Returns   
     */
    public function getUser($username){
        return $this->easemob->ApiGet('/users/'.$username);
    }

    /**
     * @Synopsis  获取IM用户[批量]
     *
     * @Param $limit
     *
     * @Returns   
     */
    public function getUsers($limit = 10,$cursor = ''){
        if(!is_numeric($limit) || $limit < 0){
            throw new \InvalidArgumentException("limit must be a positive number but $limit was given ");
        }
        if(empty($cursor)){
            return $this->easemob->ApiGet('/users?limit='.$limit);
        }else{
            return $this->easemob->ApiGet('/users?limit='.$limit.'&cursor='.$cursor);
        }
    }

    /**
     * @Synopsis  删除用户
     *
     * @Param $username
     *
     * @Returns   
     */
    public function delUser($username){
        return $this->easemob->ApiDelete('/users/'.$username);
    }

    /**
     * @Synopsis  重置IM用户密码
     *
     * @Param $username
     * @Param $password
     *
     * @Returns   
     */
    public function setPassword($username,$password){
        $params = array( 'newpassword' => $password );
        return $this->easemob->ApiPut('/users/'.$username.'/password',$params);
    }

    /**
     * @Synopsis  修改用户昵称
     *
     * @Param $username
     * @Param $nickname
     *
     * @Returns   
     */
    public function setNickname($username,$nickname){
        $params = array( 'nickname' => $nickname);
        return $this->easemob->ApiPut('/users/'.$username,$params);
    }

    /**
     * @Synopsis  给IM用户的添加好友
     *
     * @Param $ownerUsername 是要添加好友的用户名
     * @Param $friendUsername 被添加的用户名
     *
     * @Returns   
     */
    public function setContacts($ownerUsername,$friendUsername) {
        return $this->easemob->ApiPost('/users/'.$ownerUsername.'/contacts/users/'.$friendUsername);
    }

    /**
     * @Synopsis  解除IM用户的好友关系
     *
     * @Param $ownerUsername 是要添加好友的用户名
     * @Param $friendUsername 被添加的用户名
     *
     * @Returns   
     */
    public function delContacts($ownerUsername,$friendUsername) {
        return $this->easemob->ApiDelete('/users/'.$ownerUsername.'/contacts/users/'.$friendUsername);
    }

    /**
     * @Synopsis 查看某个IM用户的好友信息 
     *
     * @Param $ownerUsername 是要添加好友的用户名
     * @Param $friendUsername 被添加的用户名
     *
     * @Returns   
     */
    public function getContacts($ownerUsername) {
        return $this->easemob->ApiGet('/users/'.$ownerUsername.'/contacts/users');
    }

    /**
     * @Synopsis  获取一个IM用户的黑名单
     *
     * @Param $ownerUsername
     *
     * @Returns   
     */
    public function getBlocks($ownerUsername) {
        return $this->easemob->ApiGet('/users/'.$ownerUsername.'/blocks/users');
    }


    /**
     * @Synopsis 往IM用户的黑名单中加人 
     *
     * @Param $blocks 需要添加的用户名(字符串或数组)
     *
     * @Returns   
     */
    public function setBlocks($blocks) {
        $usernames = array();
        if(is_array($blocks)){
            $usernames['usernames'] = $blocks;
        }else if(is_string($blocks)){
            $usernames['usernames'] = array($blocks);
        }
        return $this->easemob->ApiPost('/users/'.$ownerUsername.'/blocks/users',$usernames);
    }

    /**
     * @Synopsis 从IM用户的黑名单中减人 
     *
     * @Param $blocks 需要添加的用户名(字符串或数组)
     *
     * @Returns   
     */
    public function delBlocks($ownerUsername,$blockUsername) {
        return $this->easemob->ApiDelete('/users/'.$ownerUsername.'/blocks/users',$blockUsername);
    }
}
?>
