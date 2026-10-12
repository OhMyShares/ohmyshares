import { Component, Input, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { User } from '../../../core/interfaces/user.interface';
import { RouterModule, ActivatedRoute } from '@angular/router';
import { UsersService } from '../../../core/services/users.service';
import { switchMap } from 'rxjs/operators';
import { of } from 'rxjs';

@Component({
    selector: 'app-user-card',
    templateUrl: './user-card.component.html',
    styleUrls: ['./user-card.component.scss'],
    standalone: false
})
export class UserCardComponent implements OnInit {
  @Input() user!: User;
  isLoading = false;

  constructor(
    private route: ActivatedRoute,
    private usersService: UsersService
  ) { }

  ngOnInit(): void {
    // If accessed via router, get the user ID from the route and fetch the user
    this.route.paramMap.pipe(
      switchMap(params => {
        const userId = params.get('id');
        if (userId && !this.user) {
          this.isLoading = true;
          return this.usersService.getUser(+userId);
        }
        return of(null);
      })
    ).subscribe(user => {
      if (user) {
        this.user = user;
        this.isLoading = false;
      }
    });
  }

  get userInitials(): string {
    if (!this.user || !this.user.name) return '';
    return this.user.name
      .split(' ')
      .map(name => name.charAt(0))
      .join('')
      .toUpperCase()
      .substring(0, 2);
  }
}
