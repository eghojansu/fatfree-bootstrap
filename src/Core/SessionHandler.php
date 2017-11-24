<?php

namespace App\Core;

use Base;
use Nutrition\SQL\ConnectionBuilder;
use Nutrition\SQL\Criteria;
use Nutrition\Security\UserManager;
use PDO;

class SessionHandler implements \SessionHandlerInterface
{
    /** @var string */
    private static $table = 'UserLogs';

    /** @var string */
    private $ip;

    /** @var string */
    private $agent;


    /**
     * Class constructor, pass ip and user agent to use it as session handler
     * @param string $ip
     * @param string $agent
     */
    public function __construct($ip = null, $agent = null)
    {
        $this->ip = $ip;
        $this->agent = $agent;
    }

    private static function query($sql)
    {
        return ConnectionBuilder::instance()->getConnection()->pdo()->prepare(
            str_replace('{table}', static::$table, $sql)
        );
    }

    private static function findSession($session_id)
    {
        $query = self::query('SELECT * FROM {table} WHERE SessionID = ? and Active LIMIT 1');
        $query->execute([$session_id]);

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function close()
    {
        return true;
    }

    public function destroy($session_id)
    {
        $query = self::query('UPDATE {table} SET Active = 0 WHERE SessionID = ?');
        $query->execute([$session_id]);

        return true;
    }

    public function gc($maxlifetime)
    {
        $query = self::query('UPDATE {table} SET Active = 0 WHERE Stamp+? < ?');
        $query->execute([$maxlifetime, time()]);

        return true;
    }

    public function open($save_path, $session_name)
    {
        return true;
    }

    public function read($session_id)
    {
        $session = self::findSession($session_id);

        if (empty($session)) {
            return '';
        }

        if ($session['Ip']!=$this->ip || $session['Agent']!=$this->agent) {
            $this->destroy($session_id);
            $this->close();

            $app = Base::instance();
            $app->clear('COOKIE.'.session_name());
            $app->error(403);
        }

        return $session['Data'];
    }

    public function write($session_id, $session_data)
    {
        $session = self::findSession($session_id);
        $user = UserManager::instance()->getUser();
        $data = [
            'UserID' => $user ? $user->ID : '',
            'SessionID' => $session_id,
            'Data' => $session_data,
            'Ip' => $this->ip,
            'Agent' => $this->agent,
            'Stamp' => time(),
            'Active' => 1,
        ];

        if ($session) {
            $params = Criteria::buildCriteria($data + [$session_id], true);
            $sql = 'UPDATE {table} SET '.implode('=?,', array_keys($data)).'=?'.
                ' WHERE SessionID = ?';
            array_shift($params);
            $query = self::query($sql);
            $query->execute($params);
        } else {
            $params = Criteria::buildCriteria($data);
            $sql = 'INSERT INTO {table} ('.implode(',', array_keys($data)).') VALUES '.
                '('.array_shift($params).')';
            $query = self::query($sql);
            $query->execute($params);
        }

        return true;
    }
}
