<div>
    @if (session()->has('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form wire:submit.prevent="createTeam" class="team-form">
        <div class="form-group">
            <label for="name">Team Name</label>
            <input type="text" id="name" wire:model="name" required>
            @error('name') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="color">Primary Color</label>
            <input type="color" id="color" wire:model="color">
            @error('color') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="second_color">Secondary Color</label>
            <input type="color" id="second_color" wire:model="second_color">
            @error('second_color') <span class="error">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="submit-btn">Create Team</button>
    </form>
</div>
