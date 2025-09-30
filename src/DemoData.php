<?php
namespace App;

class DemoData {
    private \PDO $db;
    private Auth $auth;
    private Medication $medication;
    private HealthData $healthData;
    private Reminder $reminder;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->auth = new Auth();
        $this->medication = new Medication();
        $this->healthData = new HealthData();
        $this->reminder = new Reminder();
    }
    
    /**
     * Create demo users and populate with sample data
     */
    public function createDemoData(): array {
        $results = [
            'success' => false,
            'message' => '',
            'users' => []
        ];
        
        try {
            // Create demo users
            $users = $this->createDemoUsers();
            
            foreach ($users as $user) {
                // Add medications
                $this->createDemoMedications($user['id']);
                
                // Add health data
                $this->createDemoHealthData($user['id']);
                
                // Add reminders
                $this->createDemoReminders($user['id']);
            }
            
            $results['success'] = true;
            $results['users'] = $users;
            $results['message'] = 'Demo data created successfully!';
            
        } catch (\Exception $e) {
            $results['message'] = 'Error creating demo data: ' . $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Clear all demo data from the database
     */
    public function clearDemoData(): bool {
        try {
            // Delete all demo users (cascade will handle related data)
            $stmt = $this->db->prepare("DELETE FROM users WHERE username LIKE 'demo_%'");
            return $stmt->execute();
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Create demo users
     */
    private function createDemoUsers(): array {
        $users = [];
        
        $demoUsers = [
            [
                'username' => 'demo_patient',
                'email' => 'demo.patient@caffeinecrash.local',
                'password' => 'demo123',
                'is_admin' => 0
            ],
            [
                'username' => 'demo_caregiver',
                'email' => 'demo.caregiver@caffeinecrash.local',
                'password' => 'demo123',
                'is_admin' => 0
            ]
        ];
        
        foreach ($demoUsers as $userData) {
            // Check if user already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->execute(['username' => $userData['username']]);
            
            if (!$stmt->fetch()) {
                // Create new user
                $passwordHash = password_hash($userData['password'], PASSWORD_ARGON2ID);
                
                $stmt = $this->db->prepare("
                    INSERT INTO users (username, email, password_hash, is_admin, is_active) 
                    VALUES (:username, :email, :password_hash, :is_admin, 1)
                ");
                
                $stmt->execute([
                    'username' => $userData['username'],
                    'email' => $userData['email'],
                    'password_hash' => $passwordHash,
                    'is_admin' => $userData['is_admin']
                ]);
                
                $userId = (int)$this->db->lastInsertId();
                $users[] = [
                    'id' => $userId,
                    'username' => $userData['username'],
                    'password' => $userData['password']
                ];
            } else {
                // User exists, get their ID
                $stmt = $this->db->prepare("SELECT id FROM users WHERE username = :username");
                $stmt->execute(['username' => $userData['username']]);
                $user = $stmt->fetch();
                $users[] = [
                    'id' => $user['id'],
                    'username' => $userData['username'],
                    'password' => $userData['password']
                ];
            }
        }
        
        return $users;
    }
    
    /**
     * Create demo medications for a user
     */
    private function createDemoMedications(int $userId): void {
        $medications = [
            [
                'name' => 'Sertraline',
                'dosage' => '50mg',
                'frequency' => 'Once daily',
                'prescriber' => 'Dr. Smith',
                'prescribed_for' => 'Anxiety and Depression',
                'notes' => 'Take in the morning with food'
            ],
            [
                'name' => 'Lisinopril',
                'dosage' => '10mg',
                'frequency' => 'Once daily',
                'prescriber' => 'Dr. Johnson',
                'prescribed_for' => 'High blood pressure',
                'notes' => 'Monitor blood pressure regularly'
            ],
            [
                'name' => 'Metformin',
                'dosage' => '500mg',
                'frequency' => 'Twice daily',
                'prescriber' => 'Dr. Williams',
                'prescribed_for' => 'Type 2 Diabetes',
                'notes' => 'Take with meals'
            ],
            [
                'name' => 'Vitamin D3',
                'dosage' => '2000 IU',
                'frequency' => 'Once daily',
                'prescriber' => 'Dr. Smith',
                'prescribed_for' => 'Vitamin D deficiency',
                'notes' => 'Over-the-counter supplement'
            ]
        ];
        
        foreach ($medications as $med) {
            $this->medication->create($userId, $med);
        }
    }
    
    /**
     * Create demo health data for a user
     */
    private function createDemoHealthData(int $userId): void {
        // Generate health data for the past 30 days
        $dataTypes = [
            ['type' => 'weight', 'unit' => 'kg', 'baseValue' => 75, 'variance' => 2],
            ['type' => 'blood_pressure_systolic', 'unit' => 'mmHg', 'baseValue' => 120, 'variance' => 10],
            ['type' => 'blood_pressure_diastolic', 'unit' => 'mmHg', 'baseValue' => 80, 'variance' => 5],
            ['type' => 'heart_rate', 'unit' => 'bpm', 'baseValue' => 72, 'variance' => 8],
            ['type' => 'blood_sugar', 'unit' => 'mg/dL', 'baseValue' => 95, 'variance' => 15]
        ];
        
        // Create entries for the past 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = date('Y-m-d H:i:s', strtotime("-$i days"));
            
            foreach ($dataTypes as $dataType) {
                if ($i % 3 == 0) { // Add data every 3 days to make it realistic
                    $value = $dataType['baseValue'] + (rand(-100, 100) / 100) * $dataType['variance'];
                    
                    $stmt = $this->db->prepare("
                        INSERT INTO health_data (user_id, data_type, value, unit, recorded_at)
                        VALUES (:user_id, :data_type, :value, :unit, :recorded_at)
                    ");
                    
                    $stmt->execute([
                        'user_id' => $userId,
                        'data_type' => $dataType['type'],
                        'value' => round($value, 1),
                        'unit' => $dataType['unit'],
                        'recorded_at' => $date
                    ]);
                }
            }
        }
        
        // Add some GAD-7 scores (anxiety assessment)
        $gad7Dates = [7, 14, 21, 28];
        foreach ($gad7Dates as $daysAgo) {
            $date = date('Y-m-d H:i:s', strtotime("-$daysAgo days"));
            $score = rand(3, 12); // Mild to moderate anxiety range
            
            $stmt = $this->db->prepare("
                INSERT INTO health_data (user_id, data_type, value, unit, recorded_at, notes)
                VALUES (:user_id, :data_type, :value, :unit, :recorded_at, :notes)
            ");
            
            $stmt->execute([
                'user_id' => $userId,
                'data_type' => 'gad7',
                'value' => $score,
                'unit' => 'score',
                'recorded_at' => $date,
                'notes' => 'Weekly anxiety assessment'
            ]);
        }
    }
    
    /**
     * Create demo reminders for a user
     */
    private function createDemoReminders(int $userId): void {
        // Get some medications for this user to link reminders to
        $meds = $this->medication->getAll($userId);
        
        $reminders = [
            [
                'title' => 'Take morning medications',
                'description' => 'Sertraline and Vitamin D',
                'remind_at' => date('Y-m-d 08:00:00', strtotime('tomorrow')),
                'medication_id' => $meds[0]['id'] ?? null
            ],
            [
                'title' => 'Take evening medications',
                'description' => 'Lisinopril and Metformin',
                'remind_at' => date('Y-m-d 20:00:00', strtotime('tomorrow')),
                'medication_id' => $meds[1]['id'] ?? null
            ],
            [
                'title' => 'Check blood pressure',
                'description' => 'Weekly blood pressure monitoring',
                'remind_at' => date('Y-m-d 09:00:00', strtotime('next monday')),
                'medication_id' => null
            ],
            [
                'title' => 'Refill prescription',
                'description' => 'Metformin prescription due for refill',
                'remind_at' => date('Y-m-d 10:00:00', strtotime('+7 days')),
                'medication_id' => $meds[2]['id'] ?? null
            ],
            [
                'title' => 'Doctor appointment',
                'description' => 'Follow-up with Dr. Smith',
                'remind_at' => date('Y-m-d 14:00:00', strtotime('+14 days')),
                'medication_id' => null
            ]
        ];
        
        foreach ($reminders as $reminder) {
            $this->reminder->create($userId, $reminder);
        }
        
        // Create some completed reminders in the past
        $completedReminders = [
            [
                'title' => 'Take morning medications',
                'description' => 'Completed',
                'remind_at' => date('Y-m-d 08:00:00', strtotime('yesterday')),
                'medication_id' => $meds[0]['id'] ?? null,
                'is_completed' => 1
            ],
            [
                'title' => 'Take evening medications',
                'description' => 'Completed',
                'remind_at' => date('Y-m-d 20:00:00', strtotime('yesterday')),
                'medication_id' => $meds[1]['id'] ?? null,
                'is_completed' => 1
            ]
        ];
        
        foreach ($completedReminders as $reminder) {
            $stmt = $this->db->prepare("
                INSERT INTO reminders (user_id, medication_id, title, description, remind_at, is_completed)
                VALUES (:user_id, :medication_id, :title, :description, :remind_at, :is_completed)
            ");
            
            $stmt->execute([
                'user_id' => $userId,
                'medication_id' => $reminder['medication_id'],
                'title' => $reminder['title'],
                'description' => $reminder['description'],
                'remind_at' => $reminder['remind_at'],
                'is_completed' => $reminder['is_completed']
            ]);
        }
    }
}
