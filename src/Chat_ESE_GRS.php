<?php

namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;


class Chat_ESE_GRS implements MessageComponentInterface
{
    private $Tipo_Clientes=array();
    ///////////////////////////////////////////////FUNCTIONS////////////////////////////////////////////////////////////////////////
    public function MostrarClientes()
    {
        echo "----------Clientes:----------\n";
        foreach ($this->clients as $client) 
        {
            echo "($client->resourceId)" . $this->Tipo_Clientes["$client->resourceId"] . "\n";
        }
        echo "-----------------------------\n";
    }
    public function Existe($Tipo) :bool
    {
       return array_key_exists($Tipo,$this->Tipo_Clientes);
    }
    public function  EsTipo($tipo,$id) : bool
    {
        return str_contains($this->Tipo_Clientes["$id"],$tipo);
    }
    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        echo"\n-----------------------------\n";
        echo "Server Iniciado.\n";
        echo"-----------------------------\n";
    }
    ///////////////////////////////////////////////EVENTOS//////////////////////////////////////////////////////////////////////////
    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        echo"\n-----------------------------\n";
        $this->clients->attach($conn);
        echo "New cliente! ({$conn->resourceId})\n";
        $this->Tipo_Clientes["$conn->resourceId"]="";
        $this->MostrarClientes();
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo "\n\n-----------------------------\ncliente:($from->resourceId)" . $this->Tipo_Clientes["$from->resourceId"] . "\n";        
        echo "LEN:(".strlen($msg);
        echo "):";
        echo "$msg\n";
        if(!strlen($msg))
            return; 
        switch($msg[0])
        {
            case chr(39)://WINDOWS
                    $toSend=chr(39);
                    $toSend[1]=chr(1);
                    echo "Asignado Typo Windows a ($from->resourceId)\n";
                    $this->Tipo_Clientes["$from->resourceId"].="[Windows]";
                    $from->send($toSend);
                    $this->MostrarClientes();
                    return;
            case chr(47)://HTML
                    $toSend=chr(47);
                    $toSend[1]=chr(1);
                    echo "Asignado Typo HTML a ($from->resourceId)\n";
                    $this->Tipo_Clientes["$from->resourceId"]="[HTML]";
                    $from->send($toSend);
                    $this->MostrarClientes();
                    return;
            case chr(35)://ESE
                echo "CODIGO->35\nSolicitando conexion cliente ESE\n";
                if( ! $this->Existe("ESE"))//if($this->ESE===-1)
                {
                    $toSend=chr(35);
                    $toSend[1]=chr(1);
                    //$this->ESE=$from;
                    $this->Tipo_Clientes["ESE"]=$from->resourceId;
                    $this->Tipo_Clientes["$from->resourceId"].="[ESE]";
                    echo "($from->resourceId)->" . $this->Tipo_Clientes["$from->resourceId"] . "\n";
                    $from->send($toSend);
                    if($this->Existe("WEBPUENTE") && ! $this->EsTipo("[WEBPUENTE]",$from->resourceId))//if($this->WEBPUENTE!==-1 && $this->WEBPUENTE!==$from)
                    {
                        echo "Avidando a WEBPUENTE de existencia ESE\n";
                        $this->Tipo_Clientes["WEBPUENTE"]->send($toSend);
                    }
                }   
                else
                {
                    echo "Denegando conexion cliente ESE\n";
                    $toSend=chr(111);
                    $toSend[1]=chr(1);
                    $from->send($toSend);
                }
                $this->MostrarClientes();
                return;
            case chr(111)://Perder!ESE
                echo "CODIGO->111\nSolicitando Perdida de ESE\n";
                if($this->Existe("ESE") && $this->EsTipo("[ESE]",$from->resourceId))
                {
                    $toSend=chr(111);
                    $toSend[1]=chr(1);
                    // if($this->WEBPUENTE!==-1 && $this->WEBPUENTE!==$this->ESE)
                    // {
                    //     $this->WEBPUENTE->send($this->toSend);
                    // }
                    // if($this->WEBPUENTE===-1 || $this->WEBPUENTE!==$this->ESE)
                    //     $this->ESE->send($this->toSend);
                    $from->send($toSend);
                    if($this->Existe("WEBPUENTE")&& ! $this->EsTipo("[WEBPUENTE]",$from->resourceId))
                    {
                        echo "Avidando a WEBPUENTE de perdida de ESE\n";
                        $this->Tipo_Clientes["WEBPUENTE"]->send($toSend);
                    }
                    unset($this->Tipo_Clientes["ESE"]);
                    $this->Tipo_Clientes["$from->resourceId"]=str_replace("[ESE]","",$this->Tipo_Clientes["$from->resourceId"]);
                    echo "($from->resourceId)-->" . $this->Tipo_Clientes["$from->resourceId"] . "\n";
                }
                else
                    echo "Denegando Perdida de USER\n";
                $this->MostrarClientes();
                return;
            case chr(51)://User 
                echo "CODIGO->51\nSolicitando conexion cliente USER\n";
                if( ! $this->Existe("USER"))//if($this->USER===-1)
                {
                    $toSend=chr(51);
                    $toSend[1]=chr(1);
                    //$this->USER=$from;
                    $this->Tipo_Clientes["USER"]=$from->resourceId;
                    $this->Tipo_Clientes["$from->resourceId"].= "[USER]";
                    echo "($from->resourceId)" . $this->Tipo_Clientes["$from->resourceId"] . "\n";
                    
                    $from->send($toSend);
                    if($this->Existe("WEBPUENTE")&& ! $this->EsTipo("[WEBPUENTE]",$from->resourceId))//if($this->WEBPUENTE!==-1 && $this->WEBPUENTE!==$from)
                    {
                        echo "Avidando a WEBPUENTE de existencia USER\n";
                        $this->Tipo_Clientes["WEBPUENTE"]->send($toSend);
                    }
                }
                else
                {
                    echo "Denegando conexion cliente USER\n";
                    $toSend=chr(55);
                    $toSend[1]=chr(1);
                    $from->send($toSend);
                }
                $this->MostrarClientes();
                return;
            case chr(55)://Perder!USER
                echo "CODIGO->55\nSolicitando Perdida de USER\n";
                if($this->Existe("USER") && $this->EsTipo("[USER]",$from->resourceId)) //if($this->USER===$from) 
                {
                    $toSend=chr(55);
                    $toSend[1]=chr(1);
                    $from->send($toSend);
                    if($this->Existe("WEBPUENTE")&& ! $this->EsTipo("[WEBPUENTE]",$from->resourceId))
                    {
                        echo "Avidando a WEBPUENTE de perdida de USER\n";
                        $this->Tipo_Clientes["WEBPUENTE"]->send($toSend);
                    }
                    $this->Tipo_Clientes["$from->resourceId"]=str_replace("[USER]","",$this->Tipo_Clientes["$from->resourceId"]);
                    
                    echo "($from->resourceId)" . $this->Tipo_Clientes["$from->resourceId"] . "\n";
                    //$this->USER=-1;
                    unset($this->Tipo_Clientes["USER"]);
                }
                else
                    echo "Denegando Perdida de USER\n";
                $this->MostrarClientes();
                return;
            case chr(107)://PuenteWeb
                echo "CODIGO->107\nSolicitando conexion cliente PUENTE_WEB\n";
                $toSend=chr(1);
                $toSend[1]=chr(1);
                if(! $this->Existe("WEBPUENTE"))//if($this->WEBPUENTE===-1)
                {
                    $toSend=chr(107);
                    $this->Tipo_Clientes["WEBPUENTE"]=$from;
                    $this->Tipo_Clientes["$from->resourceId"].="[WEBPUENTE]";
                    echo "($from->resourceId)" . $this->Tipo_Clientes["$from->resourceId"] . "\n";
                }
                else
                    echo "Denegando conexion cliente WEBPUENTE\n";
                $from->send($toSend);
                $this->MostrarClientes();
                return;            
            case chr(99)://Preguntar por Existencia
                echo "CODIGO->99\nSolicitando datos de USER y ESE\n";
                $toSend=chr(1);
                $toSend[1]=chr(1);
                if($this->EsTipo("[WEBPUENTE]",$from->resourceId))//if($from===$this->WEBPUENTE)
                {
                    $toSend[0]=chr(99);
                    if($this->Existe("ESE")&&$this->Existe("USER"))
                    {
                        echo "Informndo ESE y USER existen\n";
                        $toSend[1]=chr(4);
                    }
                    else if($this->Existe("ESE"))
                    {
                        $toSend[1]=chr(3);
                        echo " Informndo ESE existe\n";
                    }
                    else if($this->Existe("USER"))
                    {
                        $toSend[1]=chr(2);
                        echo "Informndo USER existe\n";
                    }
                    else
                    {
                        echo "Informndo No existe ni USER ni ESE\n";
                    }
                }
                else
                    echo "Solicitud denegada\n";
                $from->send($toSend);
                $this->MostrarClientes();
                return;      
            
        }
        if($this->EsTipo("[ESE]",$from->resourceId) || $this->EsTipo("[USER]",$from->resourceId))//if($from===$this->ESE||$from===$this->USER)
        {
            foreach ($this->clients as $client) 
            {
                if ( $from !== $client) 
                {
                    if($this->EsTipo("[Windows]",$client->resourceId)||$this->EsTipo("[WEBPUENTE]",$client->resourceId))
                    {
                        echo "Enviado a ($client->resourceId)".$this->Tipo_Clientes["$client->resourceId"]."\n";
                        $client->send($msg);
                    }
                   
                }
            }
        }
        $this->MostrarClientes();
    }

    public function onClose(ConnectionInterface $conn)
    {
        echo "\n-----------------------------\n($conn->resourceId)" . $this->Tipo_Clientes["$conn->resourceId"] . "\n";
        // The connection is closed, remove it, as we can no longer send it messages
        if($this->EsTipo("[WEBPUENTE]",$conn->resourceId))
        {
            unset($this->Tipo_Clientes["WEBPUENTE"]);
            echo "Cliente WEBPUENTE desconectado\n";
        }
        if($this->EsTipo("[ESE]",$conn->resourceId))
        {
            unset($this->Tipo_Clientes["ESE"]);
            if($this->Existe("WEBPUENTE"))
            {
                $toSend=chr(111);
                $toSend[1]=chr(1);
                $this->WEBPUENTE->send($toSend);
            }
            echo "Cliente ESE desconectado\n";
        }
        if($this->EsTipo("[USER]",$conn->resourceId))
        {
            unset($this->Tipo_Clientes["USER"]);
            if($this->Existe("WEBPUENTE"))
            {
                $toSend=chr(55);
                $toSend[1]=chr(1);
                $this->WEBPUENTE->send($toSend);
            }
            echo "Cliente USER desconectado\n";
        }

        unset($this->Tipo_Clientes["$conn->resourceId"]);
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
        $this->MostrarClientes();
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo"\n-----------------------------\n";
        echo $this->Tipo_Clientes["$conn->resourceId"] . ":An error has occurred: {$e->getMessage()}\n";
        $this->MostrarClientes();
        //$conn->close();
    }
}
