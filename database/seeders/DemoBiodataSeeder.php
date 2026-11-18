<?php

namespace Database\Seeders;

use App\Models\Biodata;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo data: ~40 approved biodatas with Hindi/Marathi names.
 * Run: php artisan db:seed --class=DemoBiodataSeeder   (not part of DatabaseSeeder)
 * Demo logins: phone 9000000001..9000000040, password "demo12345".
 */
class DemoBiodataSeeder extends Seeder
{
    public function run(): void
    {
        $boys = ['Rahul Gaikwad', 'Amit Wankhede', 'Suraj Bhoyar', 'Nilesh Raut', 'Pravin Dhoble', 'Sagar Meshram', 'Vikas Tidke', 'Ganesh Pawar', 'Rohit Chauhan', 'Ajay Kashyap', 'Deepak Sonawane', 'Mayur Jadhav', 'Sachin Ingle', 'Yogesh Kamble', 'Anil Bhagat', 'Kiran Waghmare', 'Mahesh Thakre', 'Prashant Lokhande', 'Vishal Rathod', 'Manoj Kumar'];
        $girls = ['Pooja Gaikwad', 'Priya Wankhede', 'Sneha Bhoyar', 'Komal Raut', 'Rutuja Dhoble', 'Neha Meshram', 'Shital Tidke', 'Anjali Pawar', 'Kavita Chauhan', 'Swati Kashyap', 'Mansi Sonawane', 'Pallavi Jadhav', 'Rupali Ingle', 'Sonali Kamble', 'Archana Bhagat', 'Divya Waghmare', 'Jyoti Thakre', 'Vaishnavi Lokhande', 'Ritu Rathod', 'Sunita Devi'];
        $gotras = ['Kashyap', 'Bharadwaj', 'Vatsa', 'Gautam', 'Atri', 'Vashishth'];
        $places = [['Nagpur', 'Kamptee'], ['Nagpur', 'Kalmeshwar'], ['Pune', 'Hadapsar'], ['Amravati', 'Badnera'], ['Wardha', 'Hinganghat'], ['Nashik', 'Satpur'], ['Bhandara', 'Tumsar'], ['Akola', 'Murtizapur'], ['Yavatmal', 'Pusad'], ['Chandrapur', 'Ballarpur']];
        $edu = [['B.A.', 'graduate'], ['B.Com', 'graduate'], ['B.E. Computer', 'professional'], ['M.A.', 'post_graduate'], ['12th', '12'], ['ITI', 'diploma'], ['MBA', 'post_graduate']];
        $jobs = [['Government clerk', 'govt'], ['Software engineer', 'private'], ['Shopkeeper', 'business'], ['Teacher', 'govt'], ['Farmer', 'farming'], ['Bank PO', 'govt'], ['Nurse', 'private'], ['Laundry business', 'business']];

        $i = 0;
        foreach ([['male', $boys], ['female', $girls]] as [$gender, $names]) {
            foreach ($names as $name) {
                $i++;
                $user = User::updateOrCreate(['phone' => '90000'.str_pad((string) $i, 5, '0', STR_PAD_LEFT)], [
                    'name' => $name, 'password' => 'demo12345', 'status' => 'active', 'language' => 'hi',
                    'gotra' => $gotras[$i % count($gotras)],
                ]);
                [$district, $area] = $places[$i % count($places)];
                [$education, $level] = $edu[$i % count($edu)];
                [$occupation, $type] = $jobs[$i % count($jobs)];
                Biodata::forceCreate([
                    'user_id' => $user->id, 'added_by' => $i % 3 === 0 ? ($gender === 'male' ? 'mother' : 'father') : 'self',
                    'status' => 'approved', 'verified' => $i % 4 === 0, 'approved_at' => now()->subDays($i),
                    'name' => $name, 'gender' => $gender,
                    'dob' => now()->subYears(21 + ($i % 12))->subDays($i * 9)->toDateString(),
                    'height_cm' => $gender === 'male' ? 165 + ($i % 20) : 150 + ($i % 18),
                    'marital_status' => $i % 15 === 0 ? 'divorced' : 'never_married',
                    'manglik' => $i % 6 === 0, 'education' => $education, 'education_level' => $level,
                    'occupation' => $occupation, 'occupation_type' => $type,
                    'income_range' => ['below_2', '2_5', '5_10'][$i % 3],
                    'father_name' => 'Shri '.explode(' ', $name)[1].' Sr.', 'mother_name' => 'Smt. '.explode(' ', $name)[1],
                    'family_type' => $i % 2 ? 'joint' : 'nuclear', 'gotra' => $gotras[$i % count($gotras)],
                    'village' => $area, 'district' => $district, 'state' => 'Maharashtra', 'current_city' => $district, 'area' => $area,
                    'about' => 'Simple, family-oriented aur samajh-daar. Samaj ki parampara ka samman karte hain.',
                    'expectation' => 'Padha-likha, sanskari aur parivar ko samajhne wala jeevansathi.',
                    'pref_age_min' => 21, 'pref_age_max' => 30, 'pref_education_level' => 'graduate', 'pref_district' => $district,
                    'contact_phone' => '90000'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                    'photo_privacy' => 'members', 'views' => 0,
                ]);
            }
        }
    }
}
