<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Transporter;
use Illuminate\Support\Facades\Hash;

class MigrateDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:data 
                            {--source-db=samariac_samaria : Base de datos fuente}
                            {--source-host=127.0.0.1 : Host de la BD fuente}
                            {--source-user= : Usuario de la BD fuente}
                            {--source-password= : Contraseña de la BD fuente}
                            {--tables=* : Tablas específicas a migrar}
                            {--dry-run : Ejecutar sin guardar datos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar datos desde el sistema actual a Laravel';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando migración de datos...');
        
        $sourceDb = $this->option('source-db');
        $sourceHost = $this->option('source-host');
        $sourceUser = $this->option('source-user');
        $sourcePassword = $this->option('source-password');
        $tables = $this->option('tables');
        $dryRun = $this->option('dry-run');
        
        if (empty($sourceUser)) {
            $this->error('Debe proporcionar --source-user');
            return Command::FAILURE;
        }
        
        try {
            // Conectar a BD fuente
            $sourceConnection = $this->createSourceConnection($sourceHost, $sourceUser, $sourcePassword, $sourceDb);
            
            if ($dryRun) {
                $this->warn('MODO DRY-RUN: No se guardarán datos');
            }
            
            // Migrar usuarios
            if (empty($tables) || in_array('users', $tables)) {
                $this->migrateUsers($sourceConnection, $dryRun);
            }
            
            // Migrar clientes
            if (empty($tables) || in_array('customers', $tables)) {
                $this->migrateCustomers($sourceConnection, $dryRun);
            }
            
            // Migrar proveedores
            if (empty($tables) || in_array('suppliers', $tables)) {
                $this->migrateSuppliers($sourceConnection, $dryRun);
            }
            
            // Migrar transportistas
            if (empty($tables) || in_array('transporters', $tables)) {
                $this->migrateTransporters($sourceConnection, $dryRun);
            }
            
            $this->info('Migración completada exitosamente!');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error durante la migración: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    private function createSourceConnection($host, $user, $password, $database)
    {
        $mysqli = mysqli_init();
        mysqli_options($mysqli, MYSQLI_OPT_CONNECT_TIMEOUT, 10);
        
        if (!@mysqli_real_connect($mysqli, $host, $user, $password, $database)) {
            throw new \Exception('Error de conexión: ' . mysqli_connect_error());
        }
        
        mysqli_set_charset($mysqli, "utf8mb4");
        
        return $mysqli;
    }
    
    private function migrateUsers($sourceConnection, $dryRun)
    {
        $this->info('Migrando usuarios...');
        
        $result = mysqli_query($sourceConnection, "SELECT * FROM sam_user WHERE user_status = 'Active'");
        $count = 0;
        
        while ($row = mysqli_fetch_assoc($result)) {
            if ($dryRun) {
                $this->line("  [DRY-RUN] Usuario: {$row['user_name']}");
                $count++;
                continue;
            }
            
            User::updateOrCreate(
                ['id' => $row['id']],
                [
                    'department' => $row['department'] ?? '',
                    'branch' => $row['branch'] ?? '',
                    'type' => $row['type'] ?? '',
                    'firstname' => $row['firstname'] ?? '',
                    'middlename' => $row['middlename'] ?? '',
                    'lastname' => $row['lastname'] ?? '',
                    'company_name' => $row['company_name'] ?? '',
                    'user_name' => $row['user_name'],
                    'user_email' => $row['user_email'] ?? '',
                    'phone_no' => $row['phone_no'] ?? '',
                    'user_password' => $row['user_password'], // Ya está hasheado
                    'user_status' => $row['user_status'] ?? 'Active',
                    'role' => $row['role'] ?? 'User',
                    'status' => $row['status'] ?? 'Old',
                    'dashboard' => $row['dashboard'] ?? '',
                    'site' => $row['site'] ?? '',
                    'date_registered' => $row['date_registered'] ?? now(),
                ]
            );
            
            $count++;
        }
        
        $this->info("  ✓ {$count} usuarios migrados");
    }
    
    private function migrateCustomers($sourceConnection, $dryRun)
    {
        $this->info('Migrando clientes...');
        
        $result = mysqli_query($sourceConnection, "SELECT * FROM sam_customer WHERE status = 'Active'");
        $count = 0;
        
        while ($row = mysqli_fetch_assoc($result)) {
            if ($dryRun) {
                $this->line("  [DRY-RUN] Cliente: {$row['company_name']}");
                $count++;
                continue;
            }
            
            Customer::updateOrCreate(
                ['id' => $row['id']],
                [
                    'customer_type' => $row['customer_type'] ?? 'company',
                    'company_name' => $row['company_name'] ?? '',
                    'firstname' => $row['firstname'] ?? '',
                    'lastname' => $row['lastname'] ?? '',
                    'tin_no' => $row['tin_no'] ?? '',
                    'withholding' => $row['withholding'] ?? 'No',
                    'withhold' => $row['withhold'] ?? '',
                    'phone_no' => $row['phone_no'] ?? '',
                    'email' => $row['email'] ?? '',
                    'contact_person' => $row['contact_person'] ?? '',
                    'office_location' => $row['office_location'] ?? '',
                    'status' => $row['status'] ?? 'Active',
                    'registered_by' => $row['registered_by'] ?? '',
                    'approved_by' => $row['approved_by'] ?? '',
                ]
            );
            
            $count++;
        }
        
        $this->info("  ✓ {$count} clientes migrados");
    }
    
    private function migrateSuppliers($sourceConnection, $dryRun)
    {
        $this->info('Migrando proveedores...');
        
        $result = mysqli_query($sourceConnection, "SELECT * FROM sam_supplier WHERE status = 'Active'");
        $count = 0;
        
        while ($row = mysqli_fetch_assoc($result)) {
            if ($dryRun) {
                $this->line("  [DRY-RUN] Proveedor: {$row['supplier_name']}");
                $count++;
                continue;
            }
            
            Supplier::updateOrCreate(
                ['id' => $row['id']],
                [
                    'supplier_name' => $row['supplier_name'] ?? '',
                    'supplier_tin' => $row['supplier_tin'] ?? '',
                    'supplier_category' => $row['supplier_category'] ?? '',
                    'contact_person' => $row['contact_person'] ?? '',
                    'phone_number' => $row['phone_number'] ?? '',
                    'status' => $row['status'] ?? 'Active',
                    'address' => $row['address'] ?? '',
                    'registered_by' => $row['registered_by'] ?? '',
                ]
            );
            
            $count++;
        }
        
        $this->info("  ✓ {$count} proveedores migrados");
    }
    
    private function migrateTransporters($sourceConnection, $dryRun)
    {
        $this->info('Migrando transportistas...');
        
        $result = mysqli_query($sourceConnection, "SELECT * FROM sam_transporter WHERE status = 'Active'");
        $count = 0;
        
        while ($row = mysqli_fetch_assoc($result)) {
            if ($dryRun) {
                $this->line("  [DRY-RUN] Transportista: {$row['company_name']}");
                $count++;
                continue;
            }
            
            Transporter::updateOrCreate(
                ['id' => $row['id']],
                [
                    'transporter_type' => $row['transporter_type'] ?? 'company',
                    'company_name' => $row['company_name'] ?? '',
                    'firstname' => $row['firstname'] ?? '',
                    'lastname' => $row['lastname'] ?? '',
                    'tin_no' => $row['tin_no'] ?? '',
                    'withholding' => $row['withholding'] ?? '',
                    'withhold' => $row['withhold'] ?? '',
                    'phone_no' => $row['phone_no'] ?? '',
                    'email' => $row['email'] ?? '',
                    'contact_person' => $row['contact_person'] ?? '',
                    'office_location' => $row['office_location'] ?? '',
                    'status' => $row['status'] ?? 'Active',
                    'registered_by' => $row['registered_by'] ?? '',
                ]
            );
            
            $count++;
        }
        
        $this->info("  ✓ {$count} transportistas migrados");
    }
}
