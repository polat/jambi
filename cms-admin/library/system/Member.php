<?php

/**
 * Class Member
 *
 * Store CRUD functions of members.
 */
class Member
{
    /**
     * Loader class instance
     * @var object $Loader
     */
    public $Loader;

    /**
     * Member constructor.
     *
     * @param $Loader Loader object
     */
    public function __construct(Loader $Loader)
    {
        $this->Loader = $Loader;
    }

    /**
     * Check if username is in use. If it's not, create new member.
     * Return a result array. It contains 2 elements:
     *  - message: Contains success or error message for alert box.
     *  - result: Type of alert box to show message.
     *
     * @access public
     * @param string $username Username of new member
     * @param string $password Password of new member
     * @param int $rank Rank of new member. 1 is user, 0 is admin.
     * @param int $status Status of new member. 0 is active, 1 is passive.
     * @return array Result array
     */
    public function addMember($username, $password, $rank, $status)
    {
        $record = $this->Loader->Db->select("SELECT `id` FROM `system_users` WHERE `username` = :username", array('username' => $username));

        if (count($record) > 0) {
            $result = array('message' => _('Bu kullanıcı adı kullanılıyor!'), 'result' => 'warning');
        } else {
            if ($this->Loader->Db->insert('system_users', array('username' => $username, 'password' => $password, 'account_status' => $status, 'rank' => $rank))) {
                $result = array('message' => _('Kullanıcı kaydı başarıyla yapıldı.'), 'result' => 'success');
            } else {
                $result = array('message' => _('Kullanıcı kaydı sırasında bir hata oluştu!'), 'result' => 'error');
            }
        }

        return $result;
    }

    /**
     * Update given member with new member parameters.
     * Return a result array. It contains 2 elements:
     *  - message: Contains success or error message for alert box.
     *  - result: Type of alert box to show message.
     *
     * @access public
     * @param int $id Id of member to update
     * @param string $username New username of user
     * @param string $password New password of user
     * @param int $rank New rank of user
     * @param int $status New status of user
     * @return array Result Array
     */
    public function updateMember($id, $username, $password, $rank, $status)
    {
        if ($this->Loader->Db->update('system_users', array('username' => $username, 'password' => $password, 'rank' => $rank, 'account_status' => $status), "id ='$id'")) {
            $result = array('message' => _('Kullanıcı bilgileri başarıyla güncellendi.'), 'result' => 'success');
        } else {
            $result = array('message' => _('Kullanıcı bilgileri güncellenirken hata oluştu!'), 'result' => 'error');
        }

        return $result;
    }

    /**
     * Delete member if given id is true.
     * Return a result array. It contains 2 elements:
     *  - message: Contains success or error message for alert box.
     *  - result: Type of alert box to show message.
     *
     * @access public
     * @param int $id Id of member to delete
     * @return array
     */
    public function deleteMember($id)
    {
        $record = $this->Loader->Db->select("SELECT `id` FROM `system_users` WHERE `id` = :id", array('id' => $id));

        if (count($record) > 0) {
            if ($this->Loader->Db->delete("system_users", "id='$id'")) {
                $result = array('message' => _('Kullanıcı başarıyla silindi.'), 'result' => 'success');
            } else {
                $result = array('message' => _('Kullanıcı silinirken hata oluştu!'), 'result' => 'error');
            }
        } else {
            $result = array('message' => _('Böyle bir kullanıcı bulunamadı!'), 'result' => 'warning');
        }

        return $result;
    }

    /**
     * Delete given list of members.
     * Return a result array. It contains 2 elements:
     *  - message: Contains success or error message for alert box.
     *  - result: Type of alert box to show message.
     *
     * @access public
     * @param array $members
     * @return array
     */
    public function deleteAllMember(array $members)
    {
        $members = explode(',', $members);
        $success = true;

        foreach ($members as $id) {
            if ($this->Loader->Db->delete("system_users", "id='$id'") == false) {
                $success = false;
            }
        }

        if ($success) {
            $result = array('message' => _('Kullanıcılar başarıyla silindi.'), 'result' => 'success');
        } else {
            $result = array('message' => _('Kullanıcılar silinirken hata oluştu!'), 'result' => 'error');
        }

        return $result;
    }

    /**
     * List all members by rank except itself and superuser.
     *
     * @access public
     * @return array List of members
     */
    public function listMembers()
    {
        return $this->Loader->Db->select("SELECT * FROM `system_users` WHERE `username` != :currentUser && `username` != 'superuser' ORDER BY `rank` ASC", array('currentUser' => $this->Loader->Session->get('user_occupant')));
    }
}