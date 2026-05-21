// UserManagement.test.tsx

import { describe, it, expect, beforeEach, vi } from "vitest";
import {
  render,
  screen,
  fireEvent,
} from "@testing-library/react";
import UserManagement from "../pages/UserManagement.tsx";

Object.assign(navigator, {
  clipboard: {
    writeText: vi.fn(),
  },
});

describe("User Management Page", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("renders page title and subtitle", () => {
    render(<UserManagement />);

    expect(
      screen.getByText("User Management")
    ).toBeInTheDocument();

    expect(
      screen.getByText(
        /Self-registration is disabled/i
      )
    ).toBeInTheDocument();
  });

  it("renders Add User button", () => {
    render(<UserManagement />);

    expect(
      screen.getByRole("button", {
        name: /Add User/i,
      })
    ).toBeInTheDocument();
  });

  it("opens add user modal", () => {
    render(<UserManagement />);

    fireEvent.click(
      screen.getByRole("button", {
        name: /Add User/i,
      })
    );

    expect(
      screen.getByRole("heading", {
        name: /Add User/i,
      })
    ).toBeInTheDocument();
  });

  it("renders initial users in table", () => {
    render(<UserManagement />);

    expect(
      screen.getByText("Priya Singh")
    ).toBeInTheDocument();

    expect(
      screen.getByText("David Okafor")
    ).toBeInTheDocument();

    expect(
      screen.getByText("Sarah Mthembu")
    ).toBeInTheDocument();
  });

  it("adds a new user to the table", async () => {
    render(<UserManagement />);

    fireEvent.click(
      screen.getByRole("button", {
        name: /Add User/i,
      })
    );

    const nameInput = screen.getByLabelText(/Full Name/i);
    const emailInput = screen.getByLabelText(/Email Address/i);

    fireEvent.change(nameInput, {
      target: { value: "Lina Ngubombi" },
    });

    fireEvent.change(emailInput, {
      target: {
        value: "lina@test.com",
      },
    });

    fireEvent.click(
      screen.getByRole("button", {
        name: /Save User/i,
      })
    );

    fireEvent.click(
      screen.getByRole("button", {
        name: /Next/i,
      })
    );

    expect(
      await screen.findByText("Lina Ngubombi")
    ).toBeInTheDocument();

    expect(
      await screen.findByText("lina@test.com")
    ).toBeInTheDocument();
  });

  it("auto-generates employee ID for staff", () => {
    render(<UserManagement />);

    fireEvent.click(
      screen.getByRole("button", {
        name: /Add User/i,
      })
    );

    const employeeInput =
      screen.getByDisplayValue(/S-/);

    expect(employeeInput).toBeInTheDocument();
  });

  it("changes employee ID when Admin selected", () => {
    render(<UserManagement />);

    fireEvent.click(
      screen.getByRole("button", {
        name: /Add User/i,
      })
    );

    fireEvent.change(
      screen.getByDisplayValue("Staff"),
      {
        target: { value: "Admin" },
      }
    );

    expect(
      screen.getByDisplayValue(/A-/)
    ).toBeInTheDocument();
  });

  it("generates a password modal after creating user", () => {
    render(<UserManagement />);

    fireEvent.click(
      screen.getByRole("button", {
        name: /Add User/i,
      })
    );

    const inputs =
      screen.getAllByRole("textbox");

    fireEvent.change(inputs[1], {
      target: { value: "Test User" },
    });

    fireEvent.change(inputs[2], {
      target: {
        value: "test@test.com",
      },
    });

    fireEvent.click(
      screen.getByRole("button", {
        name: /Save User/i,
      })
    );

    expect(
      screen.getByText(
        /Temporary Password/i
      )
    ).toBeInTheDocument();
  });

  it("opens edit modal", () => {
    render(<UserManagement />);

    const editButtons =
      screen.getAllByLabelText("Edit");

    fireEvent.click(editButtons[0]);

    expect(
      screen.getByText("Edit User")
    ).toBeInTheDocument();
  });

  it("updates user information", async () => {
    render(<UserManagement />);

    const editButtons =
      screen.getAllByLabelText("Edit");

    fireEvent.click(editButtons[0]);

    const nameInput = screen.getByLabelText(/Full Name/i);

    fireEvent.change(nameInput, {
      target: {
        value: "Updated Name",
      },
    });

    fireEvent.click(
      screen.getByRole("button", {
        name: /Save Changes/i,
      })
    );

    expect(
      await screen.findByText("Updated Name")
    ).toBeInTheDocument();
  });

  it("toggles user active/inactive status", () => {
    render(<UserManagement />);

    expect(
      screen.getAllByText("Active")[0]
    ).toBeInTheDocument();

    const disableButtons =
      screen.getAllByLabelText(
        "Disable user"
      );

    fireEvent.click(disableButtons[0]);

    expect(
      screen.getByText("Inactive")
    ).toBeInTheDocument();
  });

  it("search filters users", () => {
    render(<UserManagement />);

    const searchInput =
      screen.getByPlaceholderText(
        /Search by name/i
      );

    fireEvent.change(searchInput, {
      target: {
        value: "Priya",
      },
    });

    expect(
      screen.getByText("Priya Singh")
    ).toBeInTheDocument();

    expect(
      screen.queryByText("Mary Chen")
    ).not.toBeInTheDocument();
  });

  it("pagination buttons render", () => {
    render(<UserManagement />);

    expect(
      screen.getByText(/Prev/i)
    ).toBeInTheDocument();

    expect(
      screen.getByText(/Next/i)
    ).toBeInTheDocument();
  });

  it("copies password to clipboard", () => {
    render(<UserManagement />);

    fireEvent.click(
      screen.getByRole("button", {
        name: /Add User/i,
      })
    );

    const inputs =
      screen.getAllByRole("textbox");

    fireEvent.change(inputs[1], {
      target: { value: "Copy Test" },
    });

    fireEvent.change(inputs[2], {
      target: {
        value: "copy@test.com",
      },
    });

    fireEvent.click(
      screen.getByRole("button", {
        name: /Save User/i,
      })
    );

    fireEvent.click(
      screen.getByRole("button", {
        name: /Copy/i,
      })
    );

    expect(
      navigator.clipboard.writeText
    ).toHaveBeenCalled();
  });
});