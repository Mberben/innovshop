<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\Product;
use App\Entity\ProductImage;
use App\Entity\ProductOption;
use App\Entity\User;
use App\Enum\OrderStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Jeu de données de démonstration : php bin/console doctrine:fixtures:load
 * Comptes créés :
 *   - admin@innovshop.fr  / Admin1234!   (administrateur)
 *   - client@innovshop.fr / Client1234!  (client avec commandes)
 */
class AppFixtures extends Fixture
{
    private const CATEGORIES = [
        'audio' => ['Audio', '#1d4ed8', '#60a5fa'],
        'montres' => ['Montres connectées', '#7c3aed', '#c4b5fd'],
        'drones' => ['Drones', '#0f766e', '#5eead4'],
        'lunettes' => ['Lunettes connectées', '#b45309', '#fcd34d'],
        'maison' => ['Maison connectée', '#be123c', '#fda4af'],
    ];

    /**
     * [catégorie, nom, prix en centimes, à la une, description, spécifications, options [type => valeurs]]
     */
    private const PRODUCTS = [
        ['audio', 'Écouteurs Pulse Air', 12990, true,
            'Écouteurs sans fil à réduction de bruit active, pensés pour la ville comme pour le sport.',
            "Autonomie : 8 h (32 h avec l'étui)\nBluetooth 5.3\nRésistance IPX4\nRecharge sans fil",
            ['Couleur' => ['Noir', 'Blanc', 'Bleu nuit']]],
        ['audio', 'Casque Nova 360', 24990, false,
            'Casque circum-auriculaire avec son spatial et coussinets à mémoire de forme.',
            "Autonomie : 40 h\nRéduction de bruit adaptative\nCâble USB-C audio inclus\nPoids : 250 g",
            ['Couleur' => ['Graphite', 'Sable']]],
        ['audio', 'Enceinte Echo Mini', 7990, false,
            'Enceinte portable étanche au son étonnamment ample pour sa taille.',
            "Autonomie : 12 h\nÉtanchéité IP67\nAppairage stéréo de deux enceintes",
            ['Couleur' => ['Noir', 'Rouge', 'Vert sauge']]],
        ['montres', 'Montre Vita Sport', 19990, true,
            'Montre connectée orientée sport : GPS intégré, suivi cardiaque continu et plus de 100 activités.',
            "Écran AMOLED 1,4\"\nGPS bi-fréquence\nAutonomie : 10 jours\nÉtanche 5 ATM",
            ['Taille du boîtier' => ['42 mm', '46 mm'], 'Bracelet' => ['Silicone noir', 'Nylon vert']]],
        ['montres', 'Montre Lumen Classic', 29990, false,
            "Une montre connectée au look classique, boîtier en acier et verre saphir.",
            "Boîtier acier inoxydable\nVerre saphir\nPaiement sans contact\nAutonomie : 7 jours",
            ['Bracelet' => ['Cuir marron', 'Maille acier']]],
        ['montres', 'Bracelet Fit Band 3', 5990, false,
            "Bracelet d'activité léger : pas, sommeil, fréquence cardiaque et notifications.",
            "Autonomie : 14 jours\nÉtanche 5 ATM\nPoids : 22 g",
            ['Couleur' => ['Noir', 'Rose', 'Bleu']]],
        ['drones', 'Drone SkyLite 4K', 49990, true,
            'Drone compact de moins de 250 g qui filme en 4K stabilisée sur 3 axes.',
            "Vidéo 4K 30 i/s\nNacelle 3 axes\nAutonomie : 34 min\nPortée : 10 km",
            ['Pack' => ['Standard', 'Fly More (3 batteries)']]],
        ['drones', 'Drone Hawk Pro', 99990, false,
            'Drone professionnel avec capteur 1 pouce et détection d\'obstacles omnidirectionnelle.',
            "Capteur 1\" 20 MP\nVidéo 5,4K\nDétection d'obstacles 360°\nAutonomie : 42 min",
            ['Pack' => ['Standard', 'Fly More']]],
        ['drones', 'Mini drone Pixie', 8990, false,
            "Mini drone d'initiation, idéal pour apprendre à piloter en intérieur.",
            "Caméra HD 720p\nAutonomie : 8 min\nProtections d'hélices incluses",
            ['Couleur' => ['Jaune', 'Bleu']]],
        ['lunettes', 'Lunettes Vision AR', 59990, false,
            'Lunettes de réalité augmentée qui affichent vos notifications et itinéraires dans votre champ de vision.',
            "Affichage micro-OLED\nCaméra 12 MP\nAutonomie : 6 h\nCompatible verres correcteurs",
            ['Monture' => ['Noire', 'Écaille']]],
        ['lunettes', 'Lunettes Sound Shades', 17990, false,
            'Lunettes de soleil avec haut-parleurs intégrés dans les branches.',
            "Verres polarisés UV400\nAutonomie audio : 5 h\nMicro pour les appels",
            ['Monture' => ['Noire mate', 'Transparente']]],
        ['maison', 'Ampoule connectée Glow', 2490, false,
            "Ampoule LED connectée de 16 millions de couleurs, pilotable à la voix ou depuis l'application.",
            "Culot E27\n1100 lumens\nWi-Fi, sans passerelle",
            ['Pack' => ['1 ampoule', '3 ampoules']]],
        ['maison', 'Caméra Guard 2K', 8990, false,
            'Caméra de sécurité intérieure 2K avec vision nocturne et détection de personnes.',
            "Résolution 2K\nVision nocturne 10 m\nAudio bidirectionnel\nStockage microSD ou cloud",
            []],
        ['maison', 'Thermostat Eco Smart', 14990, false,
            'Thermostat intelligent qui apprend vos habitudes pour réduire votre facture de chauffage.',
            "Écran tactile couleur\nCompatible chaudières gaz et pompes à chaleur\nSuivi de consommation",
            ['Couleur' => ['Blanc', 'Noir']]],
        ['audio', 'Barre de son Cinema S', 34990, false,
            'Barre de son Dolby Atmos avec caisson de basses sans fil pour un vrai son de cinéma.',
            "Dolby Atmos 5.1.2\nCaisson sans fil\nHDMI eARC",
            []],
    ];

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/products')]
        private readonly string $uploadDir,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }

        // Catégories
        $categories = [];
        foreach (self::CATEGORIES as $key => [$name]) {
            $category = (new Category())
                ->setName($name)
                ->setSlug($this->slugger->slug($name)->lower());
            $manager->persist($category);
            $categories[$key] = $category;
        }

        // Produits (dates étalées pour que « derniers produits » ait un sens)
        $products = [];
        foreach (self::PRODUCTS as $i => [$catKey, $name, $price, $featured, $description, $specs, $options]) {
            $product = (new Product())
                ->setName($name)
                ->setSlug($this->slugger->slug($name)->lower())
                ->setCategory($categories[$catKey])
                ->setPrice($price)
                ->setFeatured($featured)
                ->setDescription($description)
                ->setSpecifications($specs)
                ->setCreatedAt(new \DateTimeImmutable(sprintf('-%d days', (\count(self::PRODUCTS) - $i) * 2)));

            foreach ($options as $type => $values) {
                foreach ($values as $value) {
                    $product->addOption((new ProductOption())->setType($type)->setValue($value));
                }
            }

            // Image de démonstration (SVG généré) : à remplacer par de vraies photos dans l'admin
            [, $from, $to] = self::CATEGORIES[$catKey];
            $filename = 'demo-'.$product->getSlug().'.svg';
            file_put_contents($this->uploadDir.'/'.$filename, $this->placeholderSvg($name, $categories[$catKey]->getName(), $from, $to));
            $product->addImage((new ProductImage())->setFilename($filename)->setPosition(0));

            $manager->persist($product);
            $products[] = $product;
        }

        // Utilisateurs
        $admin = (new User())
            ->setEmail('admin@innovshop.fr')
            ->setFirstName('Camille')
            ->setLastName('Leroy')
            ->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'Admin1234!'));
        $manager->persist($admin);

        $client = (new User())
            ->setEmail('client@innovshop.fr')
            ->setFirstName('Julie')
            ->setLastName('Martin')
            ->setPhone('0612345678')
            ->setAddress('12 rue des Tanneurs')
            ->setPostalCode('67500')
            ->setCity('Haguenau')
            ->setMonthlySpendingGoal(50000);
        $client->setPassword($this->hasher->hashPassword($client, 'Client1234!'));
        $manager->persist($client);

        $client2 = (new User())
            ->setEmail('thomas@exemple.fr')
            ->setFirstName('Thomas')
            ->setLastName('Bernard')
            ->setAddress('3 place Kléber')
            ->setPostalCode('67000')
            ->setCity('Strasbourg')
            ->setPhone('0698765432');
        $client2->setPassword($this->hasher->hashPassword($client2, 'Client1234!'));
        $manager->persist($client2);

        // Commandes de démonstration
        $orders = [
            [$client, [0, 3], OrderStatus::SHIPPED, '-20 days'],
            [$client, [11, 11, 12], OrderStatus::VALIDATED, '-6 days'],
            [$client, [6], OrderStatus::PENDING, '-1 day'],
            [$client2, [1, 5], OrderStatus::SHIPPED, '-12 days'],
            [$client2, [2], OrderStatus::CANCELLED, '-3 days'],
        ];
        foreach ($orders as $n => [$user, $productIndexes, $status, $date]) {
            $createdAt = new \DateTimeImmutable($date);
            $order = (new Order())
                ->setUser($user)
                ->setStatus($status)
                ->setCreatedAt($createdAt)
                ->setReference(sprintf('CMD-%s-%04d', $createdAt->format('Ymd'), $n + 1))
                ->setShippingName($user->getFirstName().' '.$user->getLastName())
                ->setShippingAddress($user->getAddress())
                ->setShippingPostalCode($user->getPostalCode())
                ->setShippingCity($user->getCity())
                ->setShippingPhone($user->getPhone());

            $total = 0;
            foreach ($productIndexes as $index) {
                $product = $products[$index];
                $option = $product->getOptions()->first() ?: null;
                $line = (new OrderLine())
                    ->setProduct($product)
                    ->setProductOption($option)
                    ->setProductName($product->getName())
                    ->setOptionLabel($option ? (string) $option : null)
                    ->setPrice($product->getPrice());
                $order->addOrderLine($line);
                $total += $product->getPrice();
            }
            $order->setTotal($total);
            $manager->persist($order);
        }

        $manager->flush();
    }

    private function placeholderSvg(string $title, string $subtitle, string $from, string $to): string
    {
        $title = htmlspecialchars($title, \ENT_XML1);
        $subtitle = htmlspecialchars($subtitle, \ENT_XML1);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600">
  <defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="$from"/><stop offset="1" stop-color="$to"/></linearGradient></defs>
  <rect width="800" height="600" fill="url(#g)"/>
  <circle cx="620" cy="140" r="180" fill="#ffffff" fill-opacity="0.12"/>
  <circle cx="160" cy="520" r="140" fill="#ffffff" fill-opacity="0.10"/>
  <text x="60" y="300" font-family="Helvetica, Arial, sans-serif" font-size="52" font-weight="700" fill="#ffffff">$title</text>
  <text x="60" y="360" font-family="Helvetica, Arial, sans-serif" font-size="28" fill="#ffffff" fill-opacity="0.85">$subtitle</text>
</svg>
SVG;
    }
}
