<?php

namespace Yespo\Integrations\Plugins;

class Newsletter
{
/*

    private $table = 'newsletter';

    public function sendUserToYespo(){
        $emails = Plugin_Dealing_Trait::getUniqeEmails($this->table);
        return $emails;
        if(!empty($emails) && is_array($emails) && count($emails) > 0){
            foreach ($emails as $email){
                return $email;

                //(new Yespo\Integrations\Esputnik\Esputnik_Contact())->create_plugin_subscribed_on_yespo(
                //    $this->getUserMap(
                //        Plugin_Dealing_Trait::getUserByEmail($this->table, $email)
                //    )
                //);

            }
        }
    }

    private function getUserMap(object $user){
        {
            $data['channels'][] = [
                'value' => $user->email,
                'type' => 'email'
            ];

            if($user->name !== null) $data['firstName'] = $user->name;
            else $data['firstName'] = ' ';

            if($user->surname !== null) $data['lastName'] = $user->surname;
            else $data['lastName'] = ' ';

            if($user->country !== null) $country = $user->country;
            else $country = ' ';

            if($user->region !== null) $country .= ' ' . $user->region;

            if($user->city !== null) $city = $user->city;
            else $city = ' ';

            $data['address'] = [
                'region' => $country ?? '',
                'town' => $city ?? '',
            ];

            return $data;
        }
    }
*/
}