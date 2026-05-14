<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Enums\TicketStatus;
class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user !== null; // Hanya pengguna yang sudah login yang bisa melihat daftar tiket
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->hasRole('administrator')) {
            return true;
        }   

        if ($user->hasRole('supervisor')) {
            return $this->isAssignedToSupervisorTeam($user, $ticket);
        }

        if ($user->hasRole('agent')) {
            return $ticket->assigned_agent_id === $user->id;
        }

        // Customer: "View only tickets created by themselves"
        return $ticket->created_by === $user->id;
    }

    public function comment(User $user, Ticket $ticket): bool
    {
        if ($user->hasRole(['administrator', 'supervisor'])) {
            return true;
        }   

        if ($user->hasRole('agent')) {
            return $ticket->assigned_agent_id === $user->id;
        }

        // Customer: "View only tickets created by themselves"
        return $ticket->created_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole(['administrator', 'customer'])) {
            return true;
        }   
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // Admin: "Create, edit, and manage tickets"
        if ($user->hasRole('administrator')) {
            return true;
        }

        // Customer: "CANNOT edit tickets after submission"
        // Agent: "CANNOT edit data dasar (hanya update status/comment)"
        return false;
    }

        /**
     * Menentukan apakah pengguna bisa mengubah status tiket.
     */
    public function changeStatus(User $user, Ticket $ticket): bool
    {
        if ($user->hasRole('administrator')) {
            return true;
        }

        if ($user->hasRole('agent')) {
            return $ticket->assigned_agent_id === $user->id;
        }

        if ($user->hasRole('supervisor')) {
            return $this->isAssignedToSupervisorTeam($user, $ticket);
        }

        if ($user->hasRole('customer')) {
            return $ticket->created_by === $user->id && 
                in_array($ticket->status, [TicketStatus::RESOLVED, TicketStatus::CLOSED]);
        }
        
        return false;
    }


    /**
     * Menentukan apakah pengguna bisa menghapus tiket.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        // Spesifikasi: Customer, Agent, Supervisor "CANNOT delete tickets"
        // Hanya Admin yang bisa (sebagai bagian dari 'manage tickets')
        return $user->hasRole('administrator');
    }

    public function viewInternalNotes(User $user, Ticket $ticket): bool
    {
        if ($user->hasRole('administrator')) {
            return true;
        }   
        if ($user->hasRole('supervisor')) {
            return $this->isAssignedToSupervisorTeam($user, $ticket);
        }
        if ($user->hasRole('agent')) {
            return $ticket->assigned_agent_id === $user->id;
        }
        return false;
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        if ($user->hasRole('administrator')) {
            return true;
        }

        if ($user->hasRole('supervisor')) {
            return $this->isAssignedToSupervisorTeam($user, $ticket);
        }

        return false;
    }

    public function reassign(User $user, Ticket $ticket): bool
    {
        return $this->assign($user, $ticket);
    }

    public function upload(User $user, Ticket $ticket): bool
    {
        // Izinkan jika pengguna adalah administrator atau supervisor
        if ($user->hasRole(['administrator', 'supervisor'])) {
            return true;
        }

        // Izinkan jika pengguna adalah pembuat tiket atau agen yang ditugaskan
        return $user->id === $ticket->created_by || $user->id === $ticket->assigned_agent_id;
    }

    private function isAssignedToSupervisorTeam(User $user, Ticket $ticket): bool
    {
        if ($user->team_id === null || $ticket->assignedAgent === null) {
            return false;
        }

        return $ticket->assignedAgent->team_id === $user->team_id;
    }
}
