<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Availability;
use App\Entity\Desk;
use App\Entity\Equipment;
use App\Entity\Reservation;
use App\Entity\Space;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create Users
        $users = $this->createUsers($manager);

        // Create Equipment
        $equipments = $this->createEquipments($manager);

        // Create Addresses
        $addresses = $this->createAddresses($manager);

        // Create Spaces with Availability
        $spaces = $this->createSpaces($manager, $users, $addresses);

        // Create Desks
        $desks = $this->createDesks($manager, $spaces, $equipments);

        // Create Reservations
        $this->createReservations($manager, $users, $desks);

        $manager->flush();
    }

    private function createUsers(ObjectManager $manager): array
    {
        $users = [];

        // Admin User
        $admin = new User();
        $admin->setEmail('admin@gmail.com');
        $admin->setFirstname('Admin');
        $admin->setLastname('FlexOffice');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, '12345678'));
        $manager->persist($admin);
        $users['admin'] = $admin;

        // Host User
        $host = new User();
        $host->setEmail('host@gmail.com');
        $host->setFirstname('Marie');
        $host->setLastname('Dupont');
        $host->setRoles(['ROLE_HOST']);
        $host->setIsVerified(true);
        $host->setPassword($this->passwordHasher->hashPassword($host, '12345678'));
        $manager->persist($host);
        $users['host'] = $host;

        // Additional Host
        $host2 = new User();
        $host2->setEmail('host2@gmail.com');
        $host2->setFirstname('Pierre');
        $host2->setLastname('Martin');
        $host2->setRoles(['ROLE_HOST']);
        $host2->setIsVerified(true);
        $host2->setPassword($this->passwordHasher->hashPassword($host2, '12345678'));
        $manager->persist($host2);
        $users['host2'] = $host2;

        // Guest User
        $guest = new User();
        $guest->setEmail('guest@gmail.com');
        $guest->setFirstname('Jean');
        $guest->setLastname('Durand');
        $guest->setRoles(['ROLE_GUEST']);
        $guest->setIsVerified(true);
        $guest->setPassword($this->passwordHasher->hashPassword($guest, '12345678'));
        $manager->persist($guest);
        $users['guest'] = $guest;

        // Additional Guests
        $guest2 = new User();
        $guest2->setEmail('guest2@gmail.com');
        $guest2->setFirstname('Sophie');
        $guest2->setLastname('Bernard');
        $guest2->setRoles(['ROLE_GUEST']);
        $guest2->setIsVerified(true);
        $guest2->setPassword($this->passwordHasher->hashPassword($guest2, '12345678'));
        $manager->persist($guest2);
        $users['guest2'] = $guest2;

        $guest3 = new User();
        $guest3->setEmail('guest3@gmail.com');
        $guest3->setFirstname('Thomas');
        $guest3->setLastname('Petit');
        $guest3->setRoles(['ROLE_GUEST']);
        $guest3->setIsVerified(true);
        $guest3->setPassword($this->passwordHasher->hashPassword($guest3, '12345678'));
        $manager->persist($guest3);
        $users['guest3'] = $guest3;

        return $users;
    }

    private function createEquipments(ObjectManager $manager): array
    {
        $equipments = [];

        $equipmentData = [
            ['name' => 'Écran 24"', 'description' => 'Écran externe 24 pouces Full HD'],
            ['name' => 'Clavier mécanique', 'description' => 'Clavier mécanique ergonomique'],
            ['name' => 'Souris sans fil', 'description' => 'Souris optique sans fil'],
            ['name' => 'Webcam HD', 'description' => 'Webcam haute définition pour visioconférences'],
            ['name' => 'Casque audio', 'description' => 'Casque audio avec microphone intégré'],
            ['name' => 'Station d\'accueil', 'description' => 'Station d\'accueil USB-C pour ordinateurs portables'],
            ['name' => 'Imprimante', 'description' => 'Imprimante laser couleur partagée'],
            ['name' => 'Tableau blanc', 'description' => 'Tableau blanc magnétique avec marqueurs'],
            ['name' => 'Projecteur', 'description' => 'Projecteur Full HD pour présentations'],
            ['name' => 'Système audio', 'description' => 'Système audio pour salles de réunion'],
        ];

        foreach ($equipmentData as $data) {
            $equipment = new Equipment();
            $equipment->setName($data['name']);
            $equipment->setDescription($data['description']);
            $manager->persist($equipment);
            $equipments[] = $equipment;
        }

        return $equipments;
    }

    private function createAddresses(ObjectManager $manager): array
    {
        $addresses = [];

        $addressData = [
            [
                'street' => '123 Rue de Rivoli',
                'city' => 'Paris',
                'postalCode' => '75001',
                'country' => 'France',
                'latitude' => '48.8606',
                'longitude' => '2.3376'
            ],
            [
                'street' => '45 Avenue des Champs-Élysées',
                'city' => 'Paris',
                'postalCode' => '75008',
                'country' => 'France',
                'latitude' => '48.8698',
                'longitude' => '2.3076'
            ],
            [
                'street' => '78 Boulevard Saint-Germain',
                'city' => 'Paris',
                'postalCode' => '75006',
                'country' => 'France',
                'latitude' => '48.8534',
                'longitude' => '2.3488'
            ],
            [
                'street' => '12 Rue de la République',
                'city' => 'Lyon',
                'postalCode' => '69002',
                'country' => 'France',
                'latitude' => '45.7640',
                'longitude' => '4.8357'
            ],
            [
                'street' => '34 La Canebière',
                'city' => 'Marseille',
                'postalCode' => '13001',
                'country' => 'France',
                'latitude' => '43.2965',
                'longitude' => '5.3698'
            ]
        ];

        foreach ($addressData as $data) {
            $address = new Address();
            $address->setStreet($data['street']);
            $address->setCity($data['city']);
            $address->setPostalCode($data['postalCode']);
            $address->setCountry($data['country']);
            $address->setLatitude($data['latitude']);
            $address->setLongitude($data['longitude']);
            $manager->persist($address);
            $addresses[] = $address;
        }

        return $addresses;
    }

    private function createSpaces(ObjectManager $manager, array $users, array $addresses): array
    {
        $spaces = [];

        $spaceData = [
            [
                'name' => 'Tech Hub Paris Centre',
                'description' => 'Espace de coworking moderne au cœur de Paris, équipé des dernières technologies. Idéal pour les startups et freelances tech.',
                'host' => $users['host'],
                'address' => $addresses[0],
                'availability' => [
                    'monday' => true, 'tuesday' => true, 'wednesday' => true, 'thursday' => true, 'friday' => true,
                    'saturday' => false, 'sunday' => false
                ]
            ],
            [
                'name' => 'Business Center Champs-Élysées',
                'description' => 'Centre d\'affaires prestigieux sur les Champs-Élysées. Parfait pour les réunions clients et le travail en équipe.',
                'host' => $users['host'],
                'address' => $addresses[1],
                'availability' => [
                    'monday' => true, 'tuesday' => true, 'wednesday' => true, 'thursday' => true, 'friday' => true,
                    'saturday' => true, 'sunday' => false
                ]
            ],
            [
                'name' => 'Creative Space Saint-Germain',
                'description' => 'Espace créatif dans le quartier artistique de Saint-Germain. Ambiance inspirante pour les créatifs et designers.',
                'host' => $users['host2'],
                'address' => $addresses[2],
                'availability' => [
                    'monday' => true, 'tuesday' => true, 'wednesday' => true, 'thursday' => true, 'friday' => true,
                    'saturday' => false, 'sunday' => false
                ]
            ],
            [
                'name' => 'Innovation Lab Lyon',
                'description' => 'Laboratoire d\'innovation à Lyon, équipé pour le prototypage et le développement. Communauté tech dynamique.',
                'host' => $users['host2'],
                'address' => $addresses[3],
                'availability' => [
                    'monday' => true, 'tuesday' => true, 'wednesday' => true, 'thursday' => true, 'friday' => true,
                    'saturday' => true, 'sunday' => true
                ]
            ],
            [
                'name' => 'Mediterranean Workspace',
                'description' => 'Espace de travail avec vue sur le Vieux-Port de Marseille. Ambiance méditerranéenne relaxante.',
                'host' => $users['host'],
                'address' => $addresses[4],
                'availability' => [
                    'monday' => true, 'tuesday' => true, 'wednesday' => true, 'thursday' => true, 'friday' => true,
                    'saturday' => false, 'sunday' => false
                ]
            ]
        ];

        foreach ($spaceData as $data) {
            $space = new Space();
            $space->setName($data['name']);
            $space->setDescription($data['description']);
            $space->setHost($data['host']);
            $space->setAddress($data['address']);
            $manager->persist($space);

            // Create Availability for the space
            $availability = new Availability();
            $availability->setSpace($space);
            $availability->setMonday($data['availability']['monday']);
            $availability->setTuesday($data['availability']['tuesday']);
            $availability->setWednesday($data['availability']['wednesday']);
            $availability->setThursday($data['availability']['thursday']);
            $availability->setFriday($data['availability']['friday']);
            $availability->setSaturday($data['availability']['saturday']);
            $availability->setSunday($data['availability']['sunday']);
            $manager->persist($availability);

            $spaces[] = $space;
        }

        return $spaces;
    }

    private function createDesks(ObjectManager $manager, array $spaces, array $equipments): array
    {
        $desks = [];

        // Desks for Tech Hub Paris Centre
        $deskData = [
            // Tech Hub Paris Centre
            [
                'space' => $spaces[0],
                'desks' => [
                    ['name' => 'Desk Dev-01', 'type' => Desk::DESK_TYPE_STANDARD, 'description' => 'Bureau standard avec vue sur rue, parfait pour le développement', 'price' => 35.0, 'capacity' => 1, 'equipments' => [0, 1, 2, 5]],
                    ['name' => 'Desk Dev-02', 'type' => Desk::DESK_TYPE_STANDARD, 'description' => 'Bureau standard équipé pour le travail collaboratif', 'price' => 35.0, 'capacity' => 1, 'equipments' => [0, 1, 2, 3]],
                    ['name' => 'Private Office Alpha', 'type' => Desk::DESK_TYPE_PRIVATE_OFFICE, 'description' => 'Bureau privé pour 2 personnes avec équipement complet', 'price' => 85.0, 'capacity' => 2, 'equipments' => [0, 1, 2, 3, 4, 5, 6]],
                    ['name' => 'Meeting Room Tech', 'type' => Desk::DESK_TYPE_MEETING_ROOM, 'description' => 'Salle de réunion équipée pour 6 personnes', 'price' => 120.0, 'capacity' => 6, 'equipments' => [7, 8, 9]],
                ]
            ],
            // Business Center Champs-Élysées
            [
                'space' => $spaces[1],
                'desks' => [
                    ['name' => 'Executive Desk 01', 'type' => Desk::DESK_TYPE_STANDARD, 'description' => 'Bureau exécutif avec vue sur les Champs-Élysées', 'price' => 65.0, 'capacity' => 1, 'equipments' => [0, 1, 2, 5]],
                    ['name' => 'Executive Desk 02', 'type' => Desk::DESK_TYPE_STANDARD, 'description' => 'Bureau premium dans un environnement prestigieux', 'price' => 65.0, 'capacity' => 1, 'equipments' => [0, 1, 2, 3, 4]],
                    ['name' => 'Private Suite', 'type' => Desk::DESK_TYPE_PRIVATE_OFFICE, 'description' => 'Suite privée de luxe pour 3 personnes', 'price' => 150.0, 'capacity' => 3, 'equipments' => [0, 1, 2, 3, 4, 5, 6]],
                    ['name' => 'Conference Room Elite', 'type' => Desk::DESK_TYPE_CONFERENCE_ROOM, 'description' => 'Salle de conférence haut de gamme pour 12 personnes', 'price' => 250.0, 'capacity' => 12, 'equipments' => [7, 8, 9]],
                ]
            ],
            // Creative Space Saint-Germain
            [
                'space' => $spaces[2],
                'desks' => [
                    ['name' => 'Creative Desk A', 'type' => Desk::DESK_TYPE_STANDARD, 'description' => 'Bureau créatif avec ambiance artistique', 'price' => 40.0, 'capacity' => 1, 'equipments' => [0, 1, 2]],
                    ['name' => 'Creative Desk B', 'type' => Desk::DESK_TYPE_STANDARD, 'description' => 'Espace de travail inspirant pour créatifs', 'price' => 40.0, 'capacity' => 1, 'equipments' => [0, 1, 2, 7]],
                    ['name' => 'Design Studio', 'type' => Desk::DESK_TYPE_PRIVATE_OFFICE, 'description' => 'Studio privé pour projets créatifs', 'price' => 95.0, 'capacity' => 2, 'equipments' => [0, 1, 2, 7, 8]],
                ]
            ],
            // Innovation Lab Lyon
            [
                'space' => $spaces[3],
                'desks' => [
                    ['name' => 'Lab Station 01', 'type' => Desk::DESK_TYPE_STANDARD, 'description' => 'Station de travail pour innovation et prototypage', 'price' => 30.0, 'capacity' => 1, 'equipments' => [0, 1, 2, 5]],
                    ['name' => 'Lab Station 02', 'type' => Desk::DESK_TYPE_STANDARD, 'description' => 'Poste équipé pour le développement technique', 'price' => 30.0, 'capacity' => 1, 'equipments' => [0, 1, 2, 3, 5]],
                    ['name' => 'Innovation Pod', 'type' => Desk::DESK_TYPE_PRIVATE_OFFICE, 'description' => 'Espace privé pour équipes d\'innovation', 'price' => 75.0, 'capacity' => 4, 'equipments' => [0, 1, 2, 3, 4, 5, 7]],
                    ['name' => 'Collaboration Hub', 'type' => Desk::DESK_TYPE_MEETING_ROOM, 'description' => 'Espace collaboratif pour brainstorming', 'price' => 100.0, 'capacity' => 8, 'equipments' => [7, 8, 9]],
                ]
            ],
            // Mediterranean Workspace
            [
                'space' => $spaces[4],
                'desks' => [
                    ['name' => 'Sea View Desk', 'type' => Desk::DESK_TYPE_STANDARD, 'description' => 'Bureau avec vue sur le port de Marseille', 'price' => 45.0, 'capacity' => 1, 'equipments' => [0, 1, 2]],
                    ['name' => 'Terrace Office', 'type' => Desk::DESK_TYPE_PRIVATE_OFFICE, 'description' => 'Bureau privé avec accès terrasse', 'price' => 90.0, 'capacity' => 2, 'equipments' => [0, 1, 2, 3, 4, 5]],
                ]
            ]
        ];

        foreach ($deskData as $spaceDesks) {
            foreach ($spaceDesks['desks'] as $deskInfo) {
                $desk = new Desk();
                $desk->setSpace($spaceDesks['space']);
                $desk->setName($deskInfo['name']);
                $desk->setType($deskInfo['type']);
                $desk->setDescription($deskInfo['description']);
                $desk->setPricePerDay($deskInfo['price']);
                $desk->setCapacity($deskInfo['capacity']);
                $desk->setIsAvailable(true);

                // Add equipments
                foreach ($deskInfo['equipments'] as $equipmentIndex) {
                    if (isset($equipments[$equipmentIndex])) {
                        $desk->addEquipment($equipments[$equipmentIndex]);
                    }
                }

                $manager->persist($desk);
                $desks[] = $desk;
            }
        }

        return $desks;
    }

    private function createReservations(ObjectManager $manager, array $users, array $desks): void
    {
        // Create many sample reservations for pagination testing
        $reservationData = [];

        // Create 25 reservations for guest user to test pagination
        $guestUsers = [$users['guest'], $users['guest2'], $users['guest3']];
        $statuses = [Reservation::STATUS_CONFIRMED, Reservation::STATUS_PENDING, Reservation::STATUS_CANCELLED];

        // Future reservations (15 reservations)
        for ($i = 1; $i <= 15; $i++) {
            $reservationData[] = [
                'guest' => $guestUsers[array_rand($guestUsers)],
                'desk' => $desks[array_rand($desks)],
                'date' => "+{$i} days",
                'status' => $statuses[array_rand($statuses)]
            ];
        }

        // Past reservations (15 reservations)
        for ($i = 1; $i <= 15; $i++) {
            $reservationData[] = [
                'guest' => $guestUsers[array_rand($guestUsers)],
                'desk' => $desks[array_rand($desks)],
                'date' => "-{$i} days",
                'status' => $statuses[array_rand($statuses)]
            ];
        }

        // Add some specific reservations for the main guest user
        $specificReservations = [
            [
                'guest' => $users['guest'],
                'desk' => $desks[0], // Tech Hub - Desk Dev-01
                'date' => '+1 day',
                'status' => Reservation::STATUS_CONFIRMED
            ],
            [
                'guest' => $users['guest'],
                'desk' => $desks[1], // Tech Hub - Desk Dev-02
                'date' => '+2 days',
                'status' => Reservation::STATUS_CONFIRMED
            ],
            [
                'guest' => $users['guest'],
                'desk' => $desks[2], // Tech Hub - Private Office Alpha
                'date' => '+3 days',
                'status' => Reservation::STATUS_PENDING
            ],
            [
                'guest' => $users['guest'],
                'desk' => $desks[4], // Business Center - Executive Desk 01
                'date' => '+5 days',
                'status' => Reservation::STATUS_CONFIRMED
            ],
            [
                'guest' => $users['guest'],
                'desk' => $desks[6], // Business Center - Private Suite
                'date' => '+7 days',
                'status' => Reservation::STATUS_PENDING
            ],
        ];

        $reservationData = array_merge($reservationData, $specificReservations);

        foreach ($reservationData as $data) {
            $reservation = new Reservation();
            $reservation->setGuest($data['guest']);
            $reservation->setDesk($data['desk']);
            $reservation->setReservationDate(new \DateTime($data['date']));
            $reservation->setStatus($data['status']);
            $manager->persist($reservation);
        }
    }
}
