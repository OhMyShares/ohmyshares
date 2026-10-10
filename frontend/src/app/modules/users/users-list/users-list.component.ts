import { Component, OnInit } from '@angular/core';
import { User, UserRole } from '../../../core/interfaces/user.interface';
import { UsersService } from '../../../core/services/users.service';
import { SpinnerService } from '../../../core/services/spinner.service';

@Component({
  selector: 'app-users-list',
  templateUrl: './users-list.component.html',
  styleUrls: ['./users-list.component.scss']
})
export class UsersListComponent implements OnInit {
  users: User[] = [];
  isLoading = false;

  readonly columns = ['name', 'email', 'role', 'created_at', 'actions'];
  readonly UserRole = UserRole;

  constructor(
    private usersService: UsersService,
    private spinner$: SpinnerService
  ) {}

  ngOnInit() {
    this.loadUsers();
    this.spinner$.isLoading$.subscribe((value) => {
      this.isLoading = value;
    });
  }

  loadUsers() {
    this.usersService.getUsers().subscribe(users => {
      this.users = users;
    });
  }

  deleteUser(id: number) {
    this.usersService.deleteUser(id).subscribe(() => {
      this.loadUsers();
    });
  }
}
