<?php
/*
   ------------------------------------------------------------------------
   Plugin OS
   Copyright (C) 2016-2024 by Junior Marcati
   https://github.com/juniormarcati/os
   ------------------------------------------------------------------------
   LICENSE
   pdf file is part of Plugin OS project.
   Plugin OS is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.
   Plugin OS is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.
   You should have received a copy of the GNU Affero General Public License
   along with Plugin OS. If not, see <http://www.gnu.org/licenses/>.
   ------------------------------------------------------------------------
   @package   Plugin OS
   @author    Junior Marcati
   @co-author Edlásio Pereira
   @copyright Copyright (c) 2016-2024 OS Plugin Development team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://github.com/juniormarcati/os
   @link      https://github.com/edlasiopereira/os
   @since     2016
   ------------------------------------------------------------------------
 */
class ConfigOS
{
    private $db;
    private $empresaPlugin;
    private $cnpjPlugin;
    private $enderecoPlugin;
    private $telefonePlugin;
    private $cidadePlugin;
    private $sitePlugin;
    private $ticketId;
    private $ticketName;
    private $ticketDate;
    private $ticketCloseDate;
    private $ticketSolveDate;
    private $ticketStatus;
    private $ticketLocation;
    private $ticketContent;

    // Constructor to initialize with global $DB and set initial values
    public function __construct($db)
    {
        $this->db = $db;
        $this->fetchPluginConfig(); // Fetch configuration on initialization
        $this->fetchTicket();
        $this->getTicketCategoryName();
    }

    // Method to fetch plugin configuration
    private function fetchPluginConfig()
    {
        $query = "SELECT * FROM glpi_plugin_os_config";
        $result = $this->db->query($query);

        if ($result === false) {
            die("Error executing query: " . $this->db->error());
        }

        $pluginData = $this->db->fetchAssoc($result);

        // Set properties
        $this->empresaPlugin = $pluginData['name'];
        $this->cnpjPlugin = $pluginData['cnpj'];
        $this->enderecoPlugin = $pluginData['address'];
        $this->telefonePlugin = $pluginData['phone'];
        $this->cidadePlugin = $pluginData['city'];
        $this->sitePlugin = $pluginData['site'];
    }

    private function fetchTicket()
    {
        // Validate and sanitize input
        $ticketId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($ticketId <= 0) {
            throw new InvalidArgumentException("Invalid ticket ID");
        }
    
        // Prepare the SQL query
        $query = "
            SELECT 
                t.id AS id,
                t.name AS name,
                t.content AS content,
                t.date AS date,
                t.closedate AS closedate,
                t.solvedate AS solvedate,
                t.status AS status,
                l.name AS location_name
            FROM 
                glpi_tickets AS t
            INNER JOIN 
                glpi_locations AS l 
            ON 
                t.locations_id = l.id
            WHERE 
                t.id = $ticketId
        ";
    
        // Execute the query
        $result = $this->db->query($query);
    
        // Handle potential database errors
        if (!$result) {
            throw new RuntimeException("Database query failed: " . $this->db->error());
        }
    
        // Fetch the result
        if ($this->db->numRows($result) > 0) {
            $ticketData = $this->db->fetchAssoc($result);
    
            // Set the properties with retrieved data
            $this->ticketId = $ticketData['id'] ?? null;
            $this->ticketName = $ticketData['name'] ?? null;
            $this->ticketDate = $ticketData['date'] ?? null;
            $this->ticketCloseDate = $ticketData['closedate'] ?? null;
            $this->ticketSolveDate = $ticketData['solvedate'] ?? null;
            $this->ticketStatus = $ticketData['status'] ?? null;
            $this->ticketLocation = $ticketData['location_name'] ?? null;
            $this->ticketContent = $ticketData['content'] ?? null;
        } else {
            throw new Exception("Ticket with ID $ticketId not found");
        }
    }
    

/*     private function fetchTicket()
    {
        $query = "SELECT t.id AS id,
                    t.name AS name,
                    t.content as content,
                    t.date AS date,
                    t.closedate AS closedate,
                    t.solvedate AS solvedate,
                    t.status AS status,
                    l.name AS location_name
                    FROM glpi_tickets AS t
                    INNER JOIN glpi_locations AS l ON t.locations_id = l.id
                    WHERE t.id = '" . $_GET['id'] . "'";

        $result = $this->db->query($query);
        $ticketData = $this->db->fetchAssoc($result);

        //set property
        $this->ticketId = $ticketData['id'];
        $this->ticketName = $ticketData['name'];
        $this->ticketDate = $ticketData['date'];
        $this->ticketCloseDate = $ticketData['closedate'];
        $this->ticketSolveDate = $ticketData['solvedate'];
        $this->ticketStatus = $ticketData['status'];
        $this->ticketLocation = $ticketData['location_name'];
        $this->ticketContent = $ticketData['content'];
    } */

    // Getter and setter for $empresaPlugin
    public function getEmpresaPlugin()
    {
        return $this->empresaPlugin;
    }

    // Getter and setter for $cnpjPlugin
    public function getCnpjPlugin()
    {
        return $this->cnpjPlugin;
    }

    // Getter and setter for $enderecoPlugin
    public function getEnderecoPlugin()
    {
        return $this->enderecoPlugin;
    }

    // Getter and setter for $telefonePlugin
    public function getTelefonePlugin()
    {
        return $this->telefonePlugin;
    }

    // Getter and setter for $cidadePlugin
    public function getCidadePlugin()
    {
        return $this->cidadePlugin;
    }

    // Getter and setter for $sitePlugin
    public function getSitePlugin()
    {
        return $this->sitePlugin;
    }

    // Getter for ticketId
    public function getTicketId()
    {
        return $this->ticketId;
    }

    // Getter for ticketName
    public function getTicketName()
    {
        return $this->ticketName;
    }

    // Getter for ticketDate
    public function getTicketDate()
    {
        return $this->ticketDate;
    }

    // Getter for ticketCloseDate
    public function getTicketCloseDate()
    {
        return $this->ticketCloseDate;
    }

    // Getter for ticketSolveDate
    public function getTicketSolveDate()
    {
        return $this->ticketSolveDate;
    }

    // Getter for ticketSolveDate
    public function getTicketStatus()
    {
        $statusMapping = [
            1 => 'New',
            2 => 'Processing (Assigned)',
            3 => 'Processing (Planned)',
            4 => 'Pending',
            5 => 'Solved',
            6 => 'Closed'
        ];

        return isset($statusMapping[$this->ticketStatus]) ? $statusMapping[$this->ticketStatus] : 'Unknown';
    }

    // Getter for ticketSolveDate
    public function getTicketLocation()
    {
        $location = mb_convert_encoding($this->ticketLocation, 'UTF-8', 'auto');
        return mb_strtoupper($location, 'UTF-8');
    }

    // Getter for ticketContent
    public function getTicketContent()
    {
        $content = mb_convert_encoding($this->ticketContent, 'UTF-8', 'auto');
        return mb_strtolower($content, 'UTF-8');
    }

    // Getter for ticketSolution
    public function getTicketSolution() 
    {
        $query = "SELECT content FROM glpi_itilsolutions WHERE items_id = " . intval($_GET['id']);
        $result = $this->db->query($query);
        
        $row = $this->db->fetchAssoc($result);
        if ($row !== null) {
            return $row['content'];
        } else {
            return null; // or return an empty string, error message, etc., as appropriate
        }
    }
    
    public function getUserType($type = 1)
    { # 1 - Requester # 2 - Tecnition # 3 - Observer

        $query = "SELECT u.name 
                    from glpi_tickets as t 
                    inner join glpi_tickets_users as tu on t.id = tu.tickets_id 
                    inner join glpi_users as u on u.id = tu.users_id
                    WHERE tu.type = $type AND t.id = '" . $_GET['id'] . "'";
        $result = $this->db->query($query);

        return $this->db->fetchAssoc($result)['name'];
    }


    public function getTiketItems()
{
    $query = "SELECT 
                COALESCE(
                    CONCAT('[Computador] ', computers.name), 
                    CONCAT('[Monitor] ', monitors.name), 
                    CONCAT('[Impressora] ', printers.name), 
                    CONCAT('[Cabo] ', cables.name), 
                    CONCAT('[Telefone] ', phones.name), 
                    CONCAT('[Periferico]: ', peripherals.name), 
                    CONCAT('[Rede]: ', networkequipments.name), 
                    CONCAT('[Gabinete]: ', enclosures.name),
                    CONCAT('[Database]: ', dbase.name),
                    CONCAT('[Racks]: ', racks.name),
                    CONCAT('[DCRooms]: ', dcrooms.name),
                    CONCAT('[Software]: ', softwares.name),
                    CONCAT('[Disp. Passivo]: ', passive_equip.name),
                    CONCAT('[PDU]: ', pdus.name),
                    CONCAT('[Dispositivo]: ', appliances.name),
                    CONCAT('[Domínio]: ', domains.name),
                    CONCAT('[Certificado]: ', certificates.name),
                    CONCAT('[Cluster]: ', clusters.name),
                    CONCAT('[Inst. Database]: ', databaseinstances.name),
                    CONCAT('[Reg. Domínio]: ', domainrecords.name)
                ) AS device_name,
                COUNT(*) AS count
              FROM glpi_items_tickets AS items_tickets
              JOIN glpi_tickets AS tickets ON items_tickets.tickets_id = tickets.id
              LEFT JOIN glpi_computers AS computers ON items_tickets.items_id = computers.id AND items_tickets.itemtype = 'Computer'
              LEFT JOIN glpi_monitors AS monitors ON items_tickets.items_id = monitors.id AND items_tickets.itemtype = 'Monitor'
              LEFT JOIN glpi_printers AS printers ON items_tickets.items_id = printers.id AND items_tickets.itemtype = 'Printer'
              LEFT JOIN glpi_cables AS cables ON items_tickets.items_id = cables.id AND items_tickets.itemtype = 'Cable'
              LEFT JOIN glpi_phones AS phones ON items_tickets.items_id = phones.id AND items_tickets.itemtype = 'Phone'
              LEFT JOIN glpi_peripherals AS peripherals ON items_tickets.items_id = peripherals.id AND items_tickets.itemtype = 'Peripheral'
              LEFT JOIN glpi_networkequipments AS networkequipments ON items_tickets.items_id = networkequipments.id AND items_tickets.itemtype = 'NetworkEquipment'
              LEFT JOIN glpi_enclosures AS enclosures ON items_tickets.items_id = enclosures.id AND items_tickets.itemtype = 'Enclosure'
              LEFT JOIN glpi_databases AS dbase ON items_tickets.items_id = dbase.id AND items_tickets.itemtype = 'Database'
              LEFT JOIN glpi_racks AS racks ON items_tickets.items_id = racks.id AND items_tickets.itemtype = 'Rack'
              LEFT JOIN glpi_dcrooms AS dcrooms ON items_tickets.items_id = dcrooms.id AND items_tickets.itemtype = 'DCRoom'
              LEFT JOIN glpi_softwares AS softwares ON items_tickets.items_id = softwares.id AND items_tickets.itemtype = 'Software'
              LEFT JOIN glpi_passivedcequipments AS passive_equip ON items_tickets.items_id = passive_equip.id AND items_tickets.itemtype = 'PassiveDCEquipment'
              LEFT JOIN glpi_pdus AS pdus ON items_tickets.items_id = pdus.id AND items_tickets.itemtype = 'PDU'
              LEFT JOIN glpi_appliances AS appliances ON items_tickets.items_id = appliances.id AND items_tickets.itemtype = 'Appliance'
              LEFT JOIN glpi_domains AS domains ON items_tickets.items_id = domains.id AND items_tickets.itemtype = 'Domain'
              LEFT JOIN glpi_certificates AS certificates ON items_tickets.items_id = certificates.id AND items_tickets.itemtype = 'Certificate'
              LEFT JOIN glpi_clusters AS clusters ON items_tickets.items_id = clusters.id AND items_tickets.itemtype = 'Cluster'
              LEFT JOIN glpi_databaseinstances AS databaseinstances ON items_tickets.items_id = databaseinstances.id AND items_tickets.itemtype = 'DatabaseInstance'
              LEFT JOIN glpi_domainrecords AS domainrecords ON items_tickets.items_id = domainrecords.id AND items_tickets.itemtype = 'DomainRecord'
              WHERE tickets.id = '" . $_GET['id'] . "'
              GROUP BY device_name;";

    $result = $this->db->query($query);

    // Fetch all rows from the result set
    $items = [];
    while ($row = $this->db->fetchAssoc($result)) {
        $items[] = $row;
    }
    return $items;
}

/*public function getTicketCartridges() {
    $id = $_GET['id'];
    $query = "SELECT fields.id, cartridges.name
              FROM glpi_plugin_fields_ticketconsumiveis AS fields
              INNER JOIN glpi_cartridgeitems AS cartridges 
              ON JSON_CONTAINS(fields.cartridgeitems_id_cartridgefield, JSON_QUOTE(cast(cartridges.id as char(10))), '$')
              WHERE fields.items_id = '$id'";

    $result = $this->db->query($query);

    // Fetch all rows from the result set
    $items = [];
    while ($row = $this->db->fetchAssoc($result)) {
        $items[] = $row;
    }
   
    return $items;    
}*/

// A forma como o valor de $_GET['id'] é inserido diretamente na consulta pode ser perigosa, pois pode permitir injeção de SQL se um valor malicioso for passado para o parâmetro. O ideal 
//é usar prepared statements ou bind parameters para evitar isso.

/*public function getTicketConsumables() {
    $id = $_GET['id'];
    $query = "SELECT fields.id, consumable.name
              FROM glpi_plugin_fields_ticketconsumiveis AS fields
              INNER JOIN glpi_consumableitems AS consumable 
              ON JSON_CONTAINS(fields.consumableitems_id_consumablefield, JSON_QUOTE(cast(consumable.id as char(10))), '$')
              WHERE fields.items_id = '$id'";

    $result = $this->db->query($query);

    // Fetch all rows from the result set
    $items = [];
    while ($row = $this->db->fetchAssoc($result)) {
        $items[] = $row;
    }
    return $items;    
}*/


    public function getTicketCategoryName()
    { # 1 - Requester # 2 - Tecnition # 3 - Observer

        $query = "SELECT c.name
                    FROM glpi_tickets t
                    JOIN glpi_itilcategories c ON t.itilcategories_id = c.id
                    WHERE t.id = '" . $_GET['id'] . "'";

        $result = $this->db->query($query);
        return $this->db->fetchAssoc($result)['name'];
    }


    public function getTicketCartridges() {
    $id = $_GET['id'];
    $query = "SELECT fields.id, cartridges.name
              FROM glpi_plugin_fields_ticketconsumiveis AS fields
              INNER JOIN glpi_cartridgeitems AS cartridges 
              ON JSON_CONTAINS(fields.cartridgeitems_id_cartridgefield, JSON_QUOTE(cast(cartridges.id as char(10))), '$')
              WHERE fields.items_id = ?";

    $stmt = $this->db->prepare($query);
    $stmt->bind_param('s', $id);  // Supondo que 'id' seja do tipo string
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all rows from the result set
    $items = [];
    while ($row = $this->db->fetchAssoc($result)) {
        $items[] = $row;
    }
   
    return $items;    
}


    public function getTicketConsumables() {
        $id = $_GET['id'];
        $query = "SELECT fields.id, consumable.name
                  FROM glpi_plugin_fields_ticketconsumiveis AS fields
                  INNER JOIN glpi_consumableitems AS consumable 
                  ON JSON_CONTAINS(fields.consumableitems_id_consumablefield, JSON_QUOTE(cast(consumable.id as char(10))), '$')
                  WHERE fields.items_id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $id);  // Assumindo que 'id' é uma string
        $stmt->execute();
        $result = $stmt->get_result();
    
        // Fetch all rows from the result set
        $items = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $items[] = $row;
        }
        return $items;
    }
    

}

