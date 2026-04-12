<?php
class ProfileController extends Controller {
    private function validateAddressPayload(array $data) {
        foreach (['full_name', 'phone', 'address_line1', 'city', 'state', 'pincode'] as $field) {
            if (trim((string)$data[$field]) === '') {
                throw new Exception('Please fill in all required address fields.');
            }
        }

        if (!preg_match('/^\d{10}$/', $data['phone'])) {
            throw new Exception('Phone number must be exactly 10 digits.');
        }

        if (!preg_match('/^\d{6}$/', $data['pincode'])) {
            throw new Exception('Pincode must be exactly 6 digits.');
        }
    }

    public function index() {
        $this->requireAuth();
        $user = (new User())->find($_SESSION['user_id']);
        $this->view('profile/index', [
            'pageTitle' => 'My Profile - Vegihub',
            'currentPage' => 'profile',
            'user' => $user,
        ]);
    }

    public function update() {
        $this->requireAuth();
        $this->validateCsrf();

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if ($name === '') {
            flash('error', 'Name is required.');
            back();
        }

        if ($phone !== '' && !preg_match('/^\d{10}$/', $phone)) {
            flash('error', 'Phone number must be exactly 10 digits.');
            back();
        }

        (new User())->update($_SESSION['user_id'], ['name' => $name, 'phone' => $phone]);
        $_SESSION['user']['name'] = $name;

        flash('success', 'Profile updated successfully.');
        redirect(base_url('profile'));
    }

    public function updateAvatar() {
        $this->requireAuth();
        $this->validateCsrf();

        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Please select an image to upload.');
            back();
        }

        $filename = $this->uploadFile($_FILES['avatar'], 'avatars');
        if (!$filename) {
            back();
        }

        (new User())->update($_SESSION['user_id'], ['avatar' => $filename]);
        $_SESSION['user']['avatar'] = $filename;

        flash('success', 'Avatar updated.');
        redirect(base_url('profile'));
    }

    public function changePassword() {
        $this->requireAuth();
        $this->validateCsrf();

        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $user = (new User())->find($_SESSION['user_id']);
        if (!$user || !password_verify($current, $user['password'])) {
            flash('error', 'Current password is incorrect.');
            back();
        }

        if ($new !== $confirm) {
            flash('error', 'New passwords do not match.');
            back();
        }

        $passwordError = $this->getStrongPasswordError($new, 8);
        if ($passwordError !== null) {
            flash('error', $passwordError);
            back();
        }

        (new User())->update($_SESSION['user_id'], ['password' => password_hash($new, PASSWORD_BCRYPT)]);
        flash('success', 'Password changed successfully.');
        redirect(base_url('profile'));
    }

    public function addresses() {
        $this->requireAuth();
        $addresses = (new Address())->getUserAddresses($_SESSION['user_id']);
        $this->view('profile/addresses', [
            'pageTitle' => 'My Addresses - Vegihub',
            'currentPage' => 'profile',
            'addresses' => $addresses,
        ]);
    }

    public function addAddress() {
        $this->requireAuth();
        $this->validateCsrf();

        $data = [
            'user_id' => $_SESSION['user_id'],
            'label' => trim($_POST['label'] ?? 'Home') ?: 'Home',
            'full_name' => trim($_POST['full_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address_line1' => trim($_POST['address_line1'] ?? ''),
            'address_line2' => trim($_POST['address_line2'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'state' => trim($_POST['state'] ?? ''),
            'pincode' => trim($_POST['pincode'] ?? ''),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        try {
            $this->validateAddressPayload($data);
        } catch (Exception $e) {
            flash('error', $e->getMessage());
            back();
        }

        $addressModel = new Address();
        if (!$addressModel->getDefault($_SESSION['user_id'])) {
            $data['is_default'] = 1;
        }

        $addressModel->create($data);

        flash('success', 'Address added successfully.');
        $redirect = $_POST['redirect'] ?? '';
        redirect(base_url($redirect === 'checkout' ? 'checkout' : 'profile/addresses'));
    }

    public function editAddress() {
        $this->requireAuth();
        $this->validateCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $addressModel = new Address();
        $addr = $addressModel->find($id);
        if (!$addr || (int)$addr['user_id'] !== (int)$_SESSION['user_id']) {
            flash('error', 'Address not found.');
            back();
        }

        $data = [
            'label' => trim($_POST['label'] ?? $addr['label']) ?: 'Home',
            'full_name' => trim($_POST['full_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address_line1' => trim($_POST['address_line1'] ?? ''),
            'address_line2' => trim($_POST['address_line2'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'state' => trim($_POST['state'] ?? ''),
            'pincode' => trim($_POST['pincode'] ?? ''),
        ];

        try {
            $this->validateAddressPayload($data);
        } catch (Exception $e) {
            flash('error', $e->getMessage());
            back();
        }

        $addressModel->update($id, $data);

        flash('success', 'Address updated successfully.');
        redirect(base_url('profile/addresses'));
    }

    public function deleteAddress() {
        $this->requireAuth();
        $this->validateCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $addressModel = new Address();
        $addr = $addressModel->find($id);

        if ($addr && (int)$addr['user_id'] === (int)$_SESSION['user_id']) {
            $wasDefault = (int)$addr['is_default'] === 1;
            $addressModel->delete($id);

            if ($wasDefault) {
                $remaining = $addressModel->getDefault($_SESSION['user_id']);
                if ($remaining && (int)$remaining['is_default'] !== 1) {
                    $addressModel->setDefault($_SESSION['user_id'], $remaining['id']);
                }
            }
        }

        flash('success', 'Address deleted.');
        redirect(base_url('profile/addresses'));
    }

    public function setDefaultAddress() {
        $this->requireAuth();
        $this->validateCsrf();

        $id = (int)($_POST['id'] ?? 0);
        if (!(new Address())->setDefault($_SESSION['user_id'], $id)) {
            flash('error', 'Address not found.');
            redirect(base_url('profile/addresses'));
        }

        flash('success', 'Default address updated.');
        redirect(base_url('profile/addresses'));
    }
}
