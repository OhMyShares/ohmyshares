import { TuiNotificationService } from "@taiga-ui/core";
import { Component, OnInit } from '@angular/core';
import { UntypedFormControl, UntypedFormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { finalize } from 'rxjs/operators';
import { LOCALE_STRING, LOCALES } from '../../../core/constants';
import { User, UserRole } from '../../../core/interfaces/user.interface';
import { AuthService } from '../../../core/services/auth.service';
import { Common, ReferencesService } from '../../../core/services/references.service';
import { UsersService } from '../../../core/services/users.service';

@Component({
    selector: 'app-user-form',
    templateUrl: './user-form.component.html',
    styleUrls: ['./user-form.component.scss'],
    standalone: false
})
export class UserFormComponent implements OnInit {
  readonly languages = LOCALES;
  readonly roles: Common[] = [
    {id: UserRole.User, name: $localize`:@@users.role.user:User`},
    {id: UserRole.Admin, name: $localize`:@@users.role.admin:Admin`},
  ];

  readonly editForm = new UntypedFormGroup({
    name: new UntypedFormControl('', [Validators.required, Validators.maxLength(255)]),
    email: new UntypedFormControl('', [Validators.required, Validators.email, Validators.maxLength(255)]),
    password: new UntypedFormControl('', [Validators.minLength(8), Validators.maxLength(255)]),
    role: new UntypedFormControl(UserRole.User, [Validators.required]),
    locale: new UntypedFormControl(LOCALES.includes(LOCALE_STRING) ? LOCALE_STRING : 'en', [Validators.required]),
    is_test: new UntypedFormControl(false),
    is_ignore_funds_sufficiency: new UntypedFormControl(false),
  });

  user: User | null = null;
  isLoading = false;
  isSaving = false;
  isDeleting = false;

  constructor(
    public referencesService: ReferencesService,
    private usersService: UsersService,
    private authService: AuthService,
    private route: ActivatedRoute,
    private router: Router,
    private alertService: TuiNotificationService
  ) {}

  ngOnInit(): void {
    const id = this.route.snapshot.paramMap.get('id');
    if (!id) {
      return;
    }
    this.isLoading = true;
    this.usersService.getUser(+id)
      .pipe(finalize(() => this.isLoading = false))
      .subscribe(user => {
        this.user = user;
        this.editForm.patchValue({
          name: user.name,
          email: user.email,
          role: user.role ?? UserRole.User,
          locale: user.locale && LOCALES.includes(user.locale) ? user.locale : this.editForm.value.locale,
          is_test: !!user.is_test,
          is_ignore_funds_sufficiency: !!user.is_ignore_funds_sufficiency,
        });
        if (this.isSelf) {
          this.editForm.controls.role.disable();
        }
      });
  }

  get isSelf(): boolean {
    return !!this.user && this.user.email === this.authService.me$.value?.email;
  }

  save(): void {
    if (this.editForm.invalid || this.isSaving) {
      return;
    }
    const data: Partial<User> = {
      ...this.editForm.value,
      password: this.editForm.value.password || null,
    };
    const isNew = !this.user;
    const request$ = this.user
      ? this.usersService.updateUser(this.user.id, data)
      : this.usersService.createUser(data);

    this.isSaving = true;
    request$
      .pipe(finalize(() => this.isSaving = false))
      .subscribe(() => {
        const message = isNew
          ? $localize`:@@alert.user.create.message:User has been successfully created`
          : $localize`:@@alert.user.update.message:User has been successfully updated`;
        this.alertService.open(message, {appearance: 'positive'}).subscribe();
        this.router.navigate(['/users']);
      });
  }

  delete(): void {
    if (!this.user || this.isSelf) {
      return;
    }
    this.isDeleting = true;
    this.usersService.deleteUser(this.user.id)
      .pipe(finalize(() => this.isDeleting = false))
      .subscribe(() => {
        this.alertService.open($localize`:@@alert.user.delete.message:User has been successfully deleted`, {
          appearance: 'positive'
        }).subscribe();
        this.router.navigate(['/users']);
      });
  }
}
