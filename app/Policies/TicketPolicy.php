<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //viewAny() sebaiknya tidak mengembalikan false untuk semua pengguna. Berikan izin ke pengguna yang sudah autentikasi, lalu lakukan pembatasan data di query controller.
        return $user !== null; // Hanya pengguna yang sudah login yang bisa melihat daftar tiket
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->hasRole(['Administrator', 'Supervisor'])) {
            return true;
        }   

        if ($user->hasRole('Agent')) {
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
        if ($user->hasRole(['Administrator', 'Customer'])) {
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
        if ($user->hasRole('Administrator')) {
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
        if ($user->hasRole(['Administrator', 'Agent'])) {
            return true;
        }

        // Customer: "CANNOT change status directly, except reopening"
        // Logika detail transisi status harus ada di TicketStatusService
        return $ticket->created_by === $user->id;
    }


    /**
     * Menentukan apakah pengguna bisa menghapus tiket.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        // Spesifikasi: Customer, Agent, Supervisor "CANNOT delete tickets"
        // Hanya Admin yang bisa (sebagai bagian dari 'manage tickets')
        return $user->hasRole('Administrator');
    }

    /**
     * Menentukan apakah pengguna bisa melihat Internal Notes.
     */
    public function viewInternalNotes(User $user, Ticket $ticket): bool
    {
        // Customer: "NEVER see internal notes"
        return $user->hasRole(['Administrator', 'Supervisor', 'Agent']);
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        // Hanya Administrator dan Supervisor yang memiliki otoritas 
        // untuk menentukan atau mengubah Agent pada sebuah tiket.
        return $user->hasRole(['Administrator', 'Supervisor']);
    }
}
